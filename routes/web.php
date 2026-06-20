<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\GeneratedText;
use App\Models\SavedWord;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook']);

Route::get('/billing', function (Request $request) {
    return $request->user()->redirectToBillingPortal(route('dashboard'));
})->middleware('auth')->name('billing');

Route::get('/premium', function () {
    return view('premium');
})->middleware('auth')->name('premium');

Route::post('/subscribe', function (Request $request) {
    return $request->user()
        ->newSubscription('default', config('services.stripe.price_id'))
        ->checkout([
            'success_url' => route('generate') . '?upgraded=1',
            'cancel_url'  => route('premium'),
        ]);
})->middleware('auth')->name('subscribe');

Route::get('users', function () {
    abort_unless(auth()->id() === 1, 403);

    return User::all();
})->middleware(['auth', 'verified']);

Route::get('/', function () {
    return view('welcome', ['story' => session('story'), 'chinese' => session('chinese'), 'english' => session('english'), 'definitions' => session('definitions')]);
});

Route::post('/', function () {
    preg_match_all('/\p{Han}/u', request('characters', ''), $matches);

    // Dedupe and re-index so it stays a clean JSON array
    $characters = array_values(array_unique($matches[0]));
    $characters = array_slice($characters,0,1100);

    // Creates the story using the Anthropic API
    $story = getStoryFromAnthropic($userId = null, $characters)['story'];

    //post-processing of the story from Anthropic API 
    //split story into english and chinese by <hr>
    [$chinese, $english] = array_pad(explode('<hr>', $story ?? ''), 2, '');
    $chinese = trim($chinese);

    // Remove markdown bold (** **) from the English text
    $english = str_replace('**', '', $english);
    
    $definitions = getDefinitions($chinese);

    return redirect('/')
        ->with('story',       $story)
        ->with('chinese',     trim($chinese))
        ->with('english',     trim($english))
        ->with('definitions', $definitions);
})->middleware('throttle:100,1');

Route::get('/dashboard', function () {
    return redirect('/characters');
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/story', function () {
    return view('story');
})->middleware('auth')->name('generate');



// Save the pasted characters (overrides existing)
Route::post('/characters', function () {
    // Extract only Han characters — ignores spaces, punctuation, English, numbers
    preg_match_all('/\p{Han}/u', request('characters', ''), $matches);

    // Dedupe and re-index so it stays a clean JSON array
    $characters = array_values(array_unique($matches[0]));

    auth()->user()->charactersList()->updateOrCreate(
        [],                                  // empty = this user's existing row, if any
        ['characters_list' => $characters]
    );
    return redirect('/characters')
        ->with('status', count($characters) . ' characters saved.');
})->middleware('auth');

// Privacy policy
Route::view('/privacy', 'privacy')->name('privacy');

Route::get('/retention', function () {
    abort_unless(auth()->id() === 1, 403);

    $rows = DB::select('
        SELECT u.id,
               DATEDIFF(NOW(), u.created_at)             AS account_age,
               DATEDIFF(MAX(g.created_at), u.created_at) AS lifespan_days
        FROM users u
        INNER JOIN generated_texts g ON g.user_id = u.id
        WHERE u.created_at > ?
        GROUP BY u.id, u.created_at
    ', ['2026-06-05']);

    $users = collect($rows)->map(fn ($r) => (object) [
        'age'      => (int) $r->account_age,
        'lifespan' => (int) $r->lifespan_days,
    ]);

    $labels = [];
    $percents = [];
    $eligibleCounts = [];

    for ($n = 0; $n <= ((int) $users->max('age')-2); $n++) {
        $eligible = $users->filter(fn ($u) => $u->age >= $n);
        if ($eligible->isEmpty()) break;

        $retained = $eligible->filter(fn ($u) => $u->lifespan >= $n)->count();

        $labels[]         = $n;
        $percents[]       = round($retained / $eligible->count() * 100, 1);
        $eligibleCounts[] = $eligible->count();
    }

    $registered = User::count();
    $activated  = GeneratedText::distinct()->count('user_id');

    return view('retention-curve', [
        'labels'           => $labels,
        'percents'         => $percents,
        'eligibleCounts'   => $eligibleCounts,
        'totalUsers'       => $registered,
        'activationPct'    => $registered ? round($activated / $registered * 100, 1) : 0,
        'totalGenerations' => GeneratedText::count(),
    ]);
})->middleware('auth')->name('retention');

Route::post('/generate', function () {
    $user       = auth()->user();
    $characters = $user->charactersList?->characters_list ?? [];

    //if there was a request, instead use characters from characters sent in request
    if(request('characters')) {
        // Extract only Han characters — ignores spaces, punctuation, English, numbers
        preg_match_all('/\p{Han}/u', request('characters', ''), $matches);

        // Dedupe and re-index so it stays a clean JSON array
        $characters = array_values(array_unique($matches[0]));
    }

    if (empty($characters)) {
        return redirect('generate');
    }

    $variety = $user->isPremium() ? request('variety') : null;


    $response = getStoryFromAnthropic($user->id, $characters, $variety);
    

    $charList = implode(' ', $characters);

    $story = $response['story'];
    $generated = $response['generated'];

    //split story into english and chinese by <hr>
    [$chinese, $english] = array_pad(explode('<hr>', $story ?? ''), 2, '');

    $chinese = trim($chinese);

    // Remove markdown bold (** **) from the English text
    $english = str_replace('**', '', $english);

    $definitions = getDefinitions($chinese);

    return view('story', [
        'story' => ($story),
        'chinese' => trim($chinese),
        'english' => trim($english),
        'definitions' => $definitions,
        'textId' => $generated->id,
        'savedWords' => []]);


})->middleware('auth');

//Removed get generate request
/*Route::get('/generate', function () {
    $user       = auth()->user();
    $characters = $user->charactersList?->characters_list ?? [];

    // Creates the story using the Anthropic API
    $response = getStoryFromAnthropic($user->id, $characters);

    $story = $response['story'];
    $generated = $response['generated'];

    //split story into english and chinese by <hr>
    [$chinese, $english] = array_pad(explode('<hr>', $story ?? ''), 2, '');

    $chinese = trim($chinese);

    // Remove markdown bold (** **) from the English text
    $english = str_replace('**', '', $english);

    $definitions = getDefinitions($chinese);


    return view('story', [
        'story' => $story,
        'chinese' => trim($chinese),
        'english' => trim($english),
        'definitions' => $definitions,
        'textId' => $generated->id,
        'savedWords' => []]);
})->middleware('auth', 'throttle:100,1440');  // 50 generations per user per 24h; */


Route::get('/history', function () {
    $texts = auth()->user()->generatedTexts()->latest()->take(10)->get();
    return view('my-texts', ['texts' => $texts]);
})->middleware('auth')->name('history');


Route::get('/texts/{text}', function (GeneratedText $text) {
    abort_unless($text->user_id === auth()->id(), 403);
    [$chinese, $english] = array_pad(explode('<hr>', $text->generated_text ?? ''), 2, '');

    $chinese = trim($chinese);

    // Remove markdown bold (** **) from the English text
    $english = str_replace('**', '', $english);

    return view('story', [
        'story' => $text->generated_text,
        'chinese' => trim(strip_tags($chinese)),
        'english' => trim($english),
        'definitions' => getDefinitions($chinese) ?? /* re-annotate fallback */ [],
        'textId' => $text->id,
        'savedWords' => auth()->user()->savedWords()->where('generated_text_id', $text->id)->get(['id', 'word', 'pinyin', 'english']), 
    ]);
})->middleware('auth');

Route::post('/saved-words', function (Request $r) {
    $data = $r->validate([
        'generated_text_id' => 'nullable|integer|exists:generated_texts,id',
        'word'              => 'required|string|max:32',
        'pinyin'            => 'nullable|string|max:255',
        'english'           => 'nullable|string',
    ]);
    // updateOrCreate = idempotent; double-clicks return the same row
    $word = auth()->user()->savedWords()->updateOrCreate(
        ['generated_text_id' => $data['generated_text_id'] ?? null, 'word' => $data['word']],
        ['pinyin' => $data['pinyin'] ?? null, 'english' => $data['english'] ?? null],
    );
    return response()->json($word);   // includes id, needed for delete
})->middleware('auth');

Route::delete('/saved-words/{savedWord}', function (SavedWord $savedWord) {
    abort_unless($savedWord->user_id === auth()->id(), 403);   // ownership check
    $savedWord->delete();
    return response()->noContent();
})->middleware('auth');

require __DIR__.'/auth.php';




    // Show the form (pre-filled with their current list)
Route::get('/characters', function () {
    // Top 500 most common Chinese characters (Jun Da frequency list, most-common-first)
    $characters = [
        '的', '一', '是', '不', '了', '在', '人', '有', '我', '他', '这', '个', '们', '中', '来',
        '上', '大', '为', '和', '国', '地', '到', '以', '说', '时', '要', '就', '出', '会', '可',
        '也', '你', '对', '生', '能', '而', '子', '那', '得', '于', '着', '下', '自', '之', '年',
        '过', '发', '后', '作', '里', '用', '道', '行', '所', '然', '家', '种', '事', '成', '方',
        '多', '经', '么', '去', '法', '学', '如', '都', '同', '现', '当', '没', '动', '面', '起',
        '看', '定', '天', '分', '还', '进', '好', '小', '部', '其', '些', '主', '样', '理', '心',
        '她', '本', '前', '开', '但', '因', '只', '从', '想', '实', '日', '军', '者', '意', '无',
        '力', '它', '与', '长', '把', '机', '十', '民', '第', '公', '此', '已', '工', '使', '情',
        '明', '性', '知', '全', '三', '又', '关', '点', '正', '业', '外', '将', '两', '高', '间',
        '由', '问', '很', '最', '重', '并', '物', '手', '应', '战', '向', '头', '文', '体', '政',
        '美', '相', '见', '被', '利', '什', '二', '等', '产', '或', '新', '己', '制', '身', '果',
        '加', '西', '斯', '月', '话', '合', '回', '特', '代', '内', '信', '表', '化', '老', '给',
        '世', '位', '次', '度', '门', '任', '常', '先', '海', '通', '教', '儿', '原', '东', '声',
        '提', '立', '及', '比', '员', '解', '水', '名', '真', '论', '处', '走', '义', '各', '入',
        '几', '口', '认', '条', '平', '系', '气', '题', '活', '尔', '更', '别', '打', '女', '变',
        '四', '神', '总', '何', '电', '数', '安', '少', '报', '才', '结', '反', '受', '目', '太',
        '量', '再', '感', '建', '务', '做', '接', '必', '场', '件', '计', '管', '期', '市', '直',
        '德', '资', '命', '山', '金', '指', '克', '许', '统', '区', '保', '至', '队', '形', '社',
        '便', '空', '决', '治', '展', '马', '科', '司', '五', '基', '眼', '书', '非', '则', '听',
        '白', '却', '界', '达', '光', '放', '强', '即', '像', '难', '且', '权', '思', '王', '象',
        '完', '设', '式', '色', '路', '记', '南', '品', '住', '告', '类', '求', '据', '程', '北',
        '边', '死', '张', '该', '交', '规', '万', '取', '拉', '格', '望', '觉', '术', '领', '共',
        '确', '传', '师', '观', '清', '今', '切', '院', '让', '识', '候', '带', '导', '争', '运',
        '笑', '飞', '风', '步', '改', '收', '根', '干', '造', '言', '联', '持', '组', '每', '济',
        '车', '亲', '极', '林', '服', '快', '办', '议', '往', '元', '英', '士', '证', '近', '失',
        '转', '夫', '令', '准', '布', '始', '怎', '呢', '存', '未', '远', '叫', '台', '单', '影',
        '具', '罗', '字', '爱', '击', '流', '备', '兵', '连', '调', '深', '商', '算', '质', '团',
        '集', '百', '需', '价', '花', '党', '华', '城', '石', '级', '整', '府', '离', '况', '亚',
        '请', '技', '际', '约', '示', '复', '病', '息', '究', '线', '似', '官', '火', '断', '精',
        '满', '支', '视', '消', '越', '器', '容', '照', '须', '九', '增', '研', '写', '称', '企',
        '八', '功', '吗', '包', '片', '史', '委', '乎', '查', '轻', '易', '早', '曾', '除', '农',
        '找', '装', '广', '显', '吧', '阿', '李', '标', '谈', '吃', '图', '念', '六', '引', '历',
        '首', '医', '局', '突', '专', '费', '号', '尽', '另', '周', '较', '注', '语', '仅', '考',
        '落', '青', '随', '选', '列',
        ];    

    $characters_hsk_1 = [
        '爱', '八', '爸', '杯', '子', '北', '京', '本', '不', '客', '气', '菜', '茶', '吃', '出', '租',
        '车', '打', '电', '话', '大', '的', '点', '脑', '视', '影', '东', '西', '都', '读', '对', '起',
        '多', '少', '儿', '二', '饭', '馆', '飞', '机', '分', '钟', '高', '兴', '个', '工', '作', '狗',
        '汉', '语', '好', '喝', '和', '很', '后', '面', '回', '会', '火', '站', '几', '家', '叫', '今',
        '天', '九', '开', '看', '见', '块', '来', '老', '师', '了', '冷', '里', '零', '六', '妈', '吗',
        '买', '猫', '没', '关', '系', '米', '名', '字', '明', '哪', '那', '呢', '能', '你', '年', '女',
        '朋', '友', '漂', '亮', '苹', '果', '七', '前', '钱', '请', '去', '热', '人', '认', '识', '日',
        '三', '商', '店', '上', '午', '什', '么', '十', '时', '候', '是', '书', '谁', '水', '睡', '觉',
        '说', '四', '岁', '他', '她', '太', '听', '同', '学', '喂', '我', '们', '五', '喜', '欢', '下',
        '雨', '先', '生', '现', '在', '想', '小', '姐', '些', '写', '谢', '星', '期', '习', '校', '一',
        '衣', '服', '医', '院', '椅', '有', '月', '再', '怎', '样', '这', '中', '国', '住', '桌', '昨',
        '坐', '做',
    ];

    $characters_hsk_2 = [
        '吧', '白', '百', '帮', '助', '报', '纸', '比', '便', '宜', '别', '唱', '歌', '穿', '船', '次',
        '从', '错', '篮', '球', '但', '到', '得', '弟', '第', '懂', '房', '间', '非', '常', '务', '员',
        '告', '诉', '哥', '给', '公', '共', '汽', '斤', '司', '贵', '还', '孩', '号', '黑', '红', '迎',
        '答', '场', '鸡', '蛋', '件', '教', '室', '介', '绍', '近', '进', '就', '咖', '啡', '始', '考',
        '试', '可', '以', '课', '快', '乐', '累', '离', '两', '路', '旅', '游', '卖', '慢', '忙', '每',
        '妹', '门', '男', '您', '牛', '奶', '旁', '边', '跑', '步', '票', '妻', '床', '千', '晴', '让',
        '班', '身', '体', '病', '事', '情', '手', '表', '送', '所', '它', '踢', '题', '跳', '舞', '外',
        '完', '玩', '晚', '为', '问', '希', '望', '洗', '瓜', '向', '笑', '新', '姓', '休', '息', '雪',
        '颜', '色', '眼', '睛', '羊', '肉', '药', '要', '也', '已', '经', '意', '思', '因', '阴', '泳',
        '右', '鱼', '元', '远', '运', '动', '早', '张', '长', '丈', '夫', '找', '着', '真', '正', '知',
        '道', '准', '备', '自', '行', '走', '最', '左',
    ];

    $characters_hsk_3 = [
        '阿', '姨', '啊', '矮', '安', '静', '把', '搬', '办', '法', '半', '包', '饱', '方', '被', '鼻',
        '较', '赛', '必', '须', '变', '化', '示', '演', '宾', '冰', '箱', '才', '单', '参', '加', '草',
        '层', '差', '超', '市', '衬', '衫', '成', '绩', '城', '迟', '除', '厨', '春', '词', '聪', '扫',
        '算', '带', '担', '心', '糕', '当', '然', '地', '铁', '图', '灯', '低', '梯', '冬', '物', '短',
        '段', '锻', '炼', '饿', '而', '且', '耳', '朵', '发', '烧', '放', '附', '复', '敢', '感', '冒',
        '干', '净', '刚', '根', '据', '跟', '更', '园', '故', '刮', '于', '汁', '过', '害', '怕', '河',
        '板', '护', '照', '花', '画', '坏', '环', '境', '换', '黄', '议', '或', '者', '极', '乎', '记',
        '季', '节', '检', '查', '简', '健', '康', '讲', '角', '脚', '接', '街', '目', '结', '婚', '束',
        '解', '决', '借', '理', '久', '旧', '举', '句', '定', '渴', '刻', '空', '调', '口', '哭', '裤',
        '筷', '蓝', '礼', '历', '史', '脸', '练', '辆', '邻', '居', '楼', '绿', '马', '满', '帽', '条',
        '拿', '南', '难', '级', '轻', '鸟', '努', '力', '爬', '山', '盘', '胖', '啤', '酒', '葡', '萄',
        '普', '通', '其', '实', '奇', '怪', '骑', '铅', '笔', '清', '楚', '秋', '裙', '容', '易', '如',
        '伞', '网', '声', '音', '使', '世', '界', '瘦', '叔', '舒', '树', '数', '刷', '双', '平', '虽',
        '阳', '糖', '特', '疼', '提', '育', '甜', '头', '突', '腿', '碗', '万', '忘', '位', '文', '惯',
        '澡', '夏', '相', '信', '香', '蕉', '像', '鞋', '闻', '鲜', '李', '趣', '熊', '需', '选', '择',
        '镜', '求', '爷', '般', '直', '银', '应', '该', '响', '用', '戏', '又', '遇', '愿', '越', '云',
        '顾', '片', '急', '只', '终', '种', '重', '周', '末', '主', '注', '祝', '典', '己', '总', '业',
    ];

    $characters_hsk_4 = [
        '排', '全', '按', '暗', '括', '保', '证', '抱', '歉', '倍', '笨', '毕', '遍', '标', '达', '格',
        '扬', '饼', '并', '博', '士', '管', '仅', '部', '擦', '猜', '材', '料', '观', '尝', '吵', '功',
        '熟', '诚', '乘', '惊', '抽', '烟', '传', '窗', '户', '粗', '案', '扮', '扰', '印', '折', '针',
        '概', '约', '代', '替', '戴', '弹', '刀', '导', '处', '底', '址', '等', '掉', '丢', '堵', '肚',
        '断', '顿', '童', '展', '律', '翻', '译', '烦', '恼', '反', '映', '范', '围', '访', '弃', '暑',
        '假', '之', '份', '丰', '富', '风', '景', '否', '则', '符', '合', '父', '亲', '负', '责', '杂',
        '改', '燥', '各', '具', '资', '购', '够', '估', '计', '孤', '鼓', '励', '掌', '挂', '键', '众',
        '光', '广', '播', '逛', '规', '际', '程', '咳', '嗽', '海', '洋', '羞', '寒', '汗', '航', '码',
        '适', '盒', '猴', '悔', '厚', '忽', '互', '怀', '疑', '忆', '活', '泼', '获', '积', '基', '础',
        '激', '及', '即', '集', '划', '技', '术', '既', '继', '续', '寄', '油', '价', '坚', '持', '减',
        '肥', '将', '奖', '金', '降', '交', '流', '骄', '傲', '饺', '授', '受', '释', '尽', '紧', '禁',
        '止', '剧', '济', '验', '精', '彩', '神', '警', '察', '竞', '争', '竟', '究', '拒', '绝', '距',
        '虑', '科', '棵', '怜', '惜', '肯', '恐', '苦', '宽', '困', '扩', '垃', '圾', '桶', '拉', '辣',
        '懒', '浪', '费', '漫', '虎', '貌', '厉', '例', '俩', '连', '联', '凉', '聊', '另', '泪', '利',
        '留', '乱', '麻', '毛', '巾', '美', '丽', '梦', '密', '免', '民', '族', '母', '耐', '内', '龄',
        '农', '村', '弄', '暖', '偶', '尔', '列', '判', '陪', '批', '评', '皮', '肤', '脾', '篇', '骗',
        '乒', '乓', '瓶', '破', '签', '墙', '敲', '桥', '巧', '克', '戚', '松', '况', '穷', '区', '取',
        '缺', '却', '确', '群', '闹', '币', '任', '何', '扔', '仍', '入', '软', '散', '森', '林', '沙',
        '伤', '量', '稍', '微', '社', '申', '深', '甚', '至', '命', '省', '剩', '失', '败', '傅', '狮',
        '湿', '润', '食', '品', '纪', '收', '拾', '首', '售', '货', '输', '悉', '帅', '顺', '序', '硕',
        '死', '速', '度', '塑', '袋', '酸', '随', '孙', '台', '抬', '态', '谈', '汤', '躺', '趟', '讨',
        '论', '厌', '供', '醒', '填', '停', '挺', '推', '脱', '袜', '往', '危', '险', '味', '温', '章',
        '握', '污', '染', '无', '误', '吸', '引', '柿', '咸', '限', '制', '羡', '慕', '详', '细', '消',
        '效', '辛', '卡', '奋', '幸', '福', '性', '修', '许', '血', '压', '牙', '膏', '亚', '洲', '呀',
        '严', '研', '盐', '养', '邀', '钥', '匙', '叶', '页', '切', '亿', '艺', '此', '饮', '象', '赢',
        '硬', '永', '勇', '优', '秀', '幽', '默', '尤', '由', '谊', '愉', '与', '羽', '言', '预', '原',
        '谅', '圆', '阅', '允', '志', '咱', '暂', '脏', '增', '窄', '江', '招', '聘', '著', '整', '齐',
        '式', '支', '值', '职', '植', '指', '造', '质', '猪', '逐', '渐', '贺', '专', '赚', '撞', '仔',
        '组', '织', '嘴', '尊', '座',
    ];

    $characters = auth()->user()->charactersList?->characters_list ?? [];

    $vocabLists = [
        /*['vocab_list_name' => '250 Most Common Characters', 'characters' => array_slice($characters, 0, 250)],*/
        /*['vocab_list_name' => '500 Most Common Characters', 'characters' => array_slice($characters, 0, 500)],*/
        ['vocab_list_name' => '(Old) HSK Level 1',          'characters' => $characters_hsk_1],
        ['vocab_list_name' => '(Old) HSK Level 2',          'characters' => array_values(array_unique(array_merge($characters_hsk_1, $characters_hsk_2)))],
        ['vocab_list_name' => '(Old) HSK Level 3',          'characters' => array_values(array_unique(array_merge($characters_hsk_1, $characters_hsk_2, $characters_hsk_3)))],
        ['vocab_list_name' => '(Old) HSK Level 4',          'characters' => array_values(array_unique(array_merge($characters_hsk_1, $characters_hsk_2, $characters_hsk_3, $characters_hsk_4)))],
    ];


    return view('characters', ['characters' => $characters, 'vocabLists' => $vocabLists]);
})->middleware('auth')->name('characters');;

Route::get('/analzye', function() {
    abort_unless(auth()->id() === 1, 403);

    $charactersList = User::find(426)->charactersList;

    return $charactersList;
})->middleware('auth')->name('analyze');

Route::get('/outreach', function () {
    abort_unless(auth()->id() === 1, 403);

    $rows = DB::select('
        SELECT u.id, u.name, u.email,
               COUNT(g.id)                        AS gens,
               COUNT(DISTINCT DATE(g.created_at)) AS active_days,
               MAX(g.created_at)                  AS last_gen,
               u.created_at                       AS signed_up
        FROM users u
        INNER JOIN generated_texts g ON g.user_id = u.id
        GROUP BY u.id, u.name, u.email, u.created_at
    ');

    $users = collect($rows);

    // Interview targets: came back on 3+ separate days — the sticky tail
    $power = $users->filter(fn ($u) => $u->gens >= 10)
        ->sortByDesc(fn ($u) => [$u->active_days, $u->gens])
        ->values();

    return response()->json([
        'power_users'   => ['count' => $power->count(),   'users' => $power],
    ], 200, [], JSON_PRETTY_PRINT);
})->middleware('auth');

//Dashboard
Route::get('/dash', function () {
    $user = auth()->user();

    // ── texts: totals + unique characters seen ──────────────
    $texts = $user->generatedTexts()->latest()->limit(500)->pluck('generated_text');
    $textsTotal = $user->generatedTexts()->count();

    preg_match_all('/\p{Han}/u', $texts->implode(''), $m);
    $charsSeen = count(array_unique($m[0]));

    // ── activity: per-day counts, last 30 days, zero-filled ─
    $raw = $user->generatedTexts()
        ->where('created_at', '>=', now()->subDays(6)->startOfDay())
        ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
        ->groupBy('d')->pluck('c', 'd');

    $activityLabels = [];
    $activityCounts = [];
    for ($i = 6; $i >= 0; $i--) {
        $day = now()->subDays($i)->toDateString();
        $activityLabels[] = now()->subDays($i)->format('M j');
        $activityCounts[] = (int) ($raw[$day] ?? 0);
    }

    // ── streaks: walk distinct generation dates ─────────────
    $dates = $user->generatedTexts()
        ->selectRaw('DISTINCT DATE(created_at) as d')
        ->orderBy('d')->pluck('d')->all();

    $streakLongest = 0; $run = 0; $prev = null;
    foreach ($dates as $d) {
        $run = ($prev && \Carbon\Carbon::parse($d)->diffInDays($prev) === 1) ? $run + 1 : 1;
        $streakLongest = max($streakLongest, $run);
        $prev = \Carbon\Carbon::parse($d);
    }
    $last = $dates ? \Carbon\Carbon::parse(end($dates)) : null;
    $streakCurrent = ($last && $last->diffInDays(today()) <= 1) ? $run : 0;

    // ── saved words ─────────────────────────────────────────
    $wordsTotal  = $user->savedWords()->count();
    $wordsRecent = $user->savedWords()->where('created_at', '>=', now()->subWeek())->count();
    $deck        = $user->savedWords()->latest()->limit(5)->get(['id', 'word', 'pinyin', 'english']);

    // ── progress toward next milestone ──────────────────────
    $milestones = [250, 500, 1000, 2000, 3000, 4000, 5000];
    $current = count($user->charactersList?->characters_list ?? []);

    $next = collect($milestones)->first(fn ($m) => $current < $m);
    $progress = [
        'current'   => $current,
        'milestone' => $next ?? 5000,
        'pct'       => $next ? (int) round(100 * $current / $next) : 100,
        'maxed'     => $next === null,
    ];

    $myCharacters = implode(' ', $user->charactersList?->characters_list ?? []);

    // ── library: recent texts ───────────────────────────────
    $recentTexts = $user->generatedTexts()->latest()->limit(6)
    ->get(['id', 'generated_text', 'created_at'])
    ->map(function ($t) {
        $chinese = trim(strip_tags(Str::before($t->generated_text, '<hr>')));
        $english = trim(strip_tags(Str::after($t->generated_text, '<hr>')));
        $english = preg_replace('/\*+/', '', $english);   // strip markdown ** from titles

        return [
            'id'      => $t->id,
            'snippet' => mb_substr($chinese, 0, 24) . (mb_strlen($chinese) > 24 ? '…' : ''),
            'date'    => $t->created_at->format('M j'),
            'chars'   => mb_strlen(preg_replace('/[^\p{Han}]/u', '', $chinese)),
        ];
    });

    return view('dashboard', compact(
        'streakCurrent', 'streakLongest', 'charsSeen',
        'wordsTotal', 'wordsRecent', 'textsTotal',
        'activityLabels', 'activityCounts', 'progress', 'deck', 'recentTexts', 'myCharacters'
    ) + ['memberSince' => $user->created_at->format('M Y')]);
})->middleware('auth')->name('dash');

Route::get('/saved-words', function () {
    $words = auth()->user()->savedWords()->latest()->paginate(10, ['id', 'generated_text_id', 'word', 'pinyin', 'english']);

    return view('saved-words', ['words' => $words]);
})->middleware('auth')->name('saved-words');
