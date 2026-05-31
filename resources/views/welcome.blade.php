<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Open Graph (Facebook, iMessage, Slack, Discord, LinkedIn, WhatsApp…) -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="识字 · Let's Read Chinese!">
    <meta property="og:url" content="https://letsreadchinese.com">
    <meta property="og:title" content="识字 · Let's Read Chinese!">
    <meta property="og:description" content="Read short Chinese texts written from only the characters you know — try it free, no account needed.">
    <meta property="og:image" content="https://letsreadchinese.com/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="识字 · Let's Read Chinese!">
    <meta name="twitter:description" content="Read short Chinese texts written from only the characters you know.">
    <meta name="twitter:image" content="https://letsreadchinese.com/og-image.png">

    <title>识字 · Let's Read Chinese!</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Noto+Serif+SC:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-CLDQDHQB7N"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-CLDQDHQB7N');
    </script>

    @php
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

    $default_characters = array_slice($characters, 0, 250);

    $vocabLists = [
        ['vocab_list_name' => '250 Most Common Characters', 'characters' => array_slice($characters, 0, 250)],
        ['vocab_list_name' => '500 Most Common Characters', 'characters' => array_slice($characters, 0, 500)],
        ['vocab_list_name' => '(Old) HSK Level 1',          'characters' => $characters_hsk_1],
        ['vocab_list_name' => '(Old) HSK Level 2',          'characters' => array_values(array_unique(array_merge($characters_hsk_1, $characters_hsk_2)))],
        ['vocab_list_name' => '(Old) HSK Level 3',          'characters' => array_values(array_unique(array_merge($characters_hsk_1, $characters_hsk_2, $characters_hsk_3)))],
        ['vocab_list_name' => '(Old) HSK Level 4',          'characters' => array_values(array_unique(array_merge($characters_hsk_1, $characters_hsk_2, $characters_hsk_3, $characters_hsk_4)))],
    ];

    @endphp

<body class="antialiased">
    @include('layouts.loading-modal')

    <main class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-b from-[#fffdf8] via-paper to-paper2 p-8 text-ink">

        {{-- Faded background character --}}
        <div aria-hidden="true"
             class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-[48%] select-none font-serifsc text-[min(72vw,52rem)] font-bold leading-none text-ink opacity-[0.035]">
            读
        </div>

        {{-- Content --}}
        <div class="relative z-10 max-w-xl text-center motion-safe:animate-rise">
            <p class="mb-6 text-xs font-semibold uppercase tracking-[0.28em] text-seal">
                汉字阅读 · Learn by reading
            </p>

            <h1 class="relative inline-block font-serifsc text-[clamp(4rem,18vw,9rem)] font-bold leading-none tracking-[0.06em]">
                识字
                <span class="absolute -right-4 top-2 h-3 w-3 rotate-45 rounded-sm bg-seal"></span>
            </h1>

            <p class="mt-4 text-sm uppercase tracking-[0.36em] text-ink-soft">Let's Read Chinese!</p>

            <p class="mx-auto mt-7 max-w-md text-lg leading-relaxed text-ink-soft">
                Practice reading Chinese with customized texts —
                only read the characters you know.
            </p>

            <div class="mt-10 flex flex-wrap justify-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="inline-flex items-center justify-center rounded-full bg-seal px-7 py-3.5 font-semibold text-white shadow-[0_10px_22px_-10px_rgba(192,57,43,0.55)] transition hover:-translate-y-0.5 hover:bg-seal-deep">
                        继续阅读 · Continue
                    </a>
                @else
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center rounded-full bg-seal px-7 py-3.5 font-semibold text-white shadow-[0_10px_22px_-10px_rgba(192,57,43,0.55)] transition hover:-translate-y-0.5 hover:bg-seal-deep">
                            开始阅读 · Sign up
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center rounded-full border border-line bg-white/60 px-7 py-3.5 font-semibold text-ink transition hover:-translate-y-0.5 hover:border-ink-soft">
                            登录 · Log in
                        </a>
                    @endif
                @endauth
            </div>
        </div>

        {{-- Try it out banner --}}
        <div id="try-it-banner" class="relative z-20  mt-20 w-full max-w-2xl px-4 sm:px-6 lg:px-8 -rotate-2 lg:-translate-x-24 lx:-translate-x-24">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-seal to-seal-deep px-6 py-4 text-white shadow-lg">

                {{-- watermark --}}
                <div aria-hidden="true"
                    class="pointer-events-none absolute -right-3 -top-5 select-none font-serifsc text-[7rem] leading-none text-white/10">
                    试
                </div>

                @if(empty($story))
                <div class="relative">
                    <h2 class="mt-1 font-serifsc text-3xl font-bold">Try it out!</h2>
                    <p class="mt-1 text-sm text-white/80">Generate a text using the characters below</p>
                </div>
                @else
                <div class="relative">
                    <h2 class="mt-1 font-serifsc text-3xl font-bold">Here's your text!</h2>
                    <p class="mt-1 text-sm text-white/80">Generated using characters from the library you provided</p>
                </div>
                @endif
            </div>
        </div>
        
        {{-- Try it out --}}

        <div class="relative z-10 -mt-4 mb-12 opacity-90">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                @if(empty($story))
                <div class="overflow-hidden rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">

                    @if (session('status'))
                        <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ url('/') }}">
                        @csrf

                        <p class="mt-1 text-sm text-gray-500">
                            Use these characters or paste characters you know — we'll write you a text using only those. Spaces, punctuation, and non-Chinese text are ignored — only Chinese characters are used. 
                        </p>

                        {{-- Preset character sets --}}
                        <div class="mt-5 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                            @foreach ($vocabLists as $i => $list)
                                <label class="relative block cursor-pointer">
                                    <input onclick="fillCharactersTextBox()" data-characters-list-name="{{ $list['vocab_list_name'] }}" type="radio" name="vocab_set" value="{{ $i }}" @checked($i === 0)
                                        class="peer absolute left-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 accent-seal">
                                    <div class="flex items-center gap-3 rounded-lg border border-line bg-white py-3 pl-10 pr-3.5 text-ink transition hover:border-ink-soft peer-checked:border-seal peer-checked:bg-seal/5 peer-checked:text-seal-deep">
                                        <span class="text-sm leading-tight">{{ $list['vocab_list_name'] }}</span>
                                        <span class="ml-auto font-serifsc text-xs">{{ count($list['characters']) }} 字</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>


                        <textarea name="characters" id="characters" rows="10"
                            class="mt-3 block w-full rounded-lg border-gray-300 font-serifsc text-lg leading-relaxed focus:border-seal focus:ring-seal"
                            placeholder="你好我是中国人…">{{ implode(' ', $default_characters) }}</textarea>

                        <div class="my-4 flex items-center justify-center md:justify-between">
                            <span id="characters-textbox-count" class="text-sm text-gray-500 hidden md:inline">
                                Currently using: {{ count($default_characters) }} characters
                            </span>

                            <button type="submit"
                                onclick="document.getElementById('loading-modal').classList.remove('hidden')"
                                class="inline-flex items-center rounded-lg bg-indigo-500 px-6 py-2.5 font-semibold text-white shadow-sm transition hover:bg-indigo-600">
                                <span class="hidden md:inline"></span>Generate a Text
                            </button>
                        </div>
                    </form>

                </div>
                @else
                <x-chinese-passage-article :story="$story" />
                @endif
            </div>
        </div>

        {{-- Sign-up CTA — only after a text has been generated --}}
        @if (!empty($story))
            <div class="relative z-10 mb-16 flex flex-col items-center gap-4 text-center motion-safe:animate-rise">
                <p class="max-w-sm text-sm leading-relaxed text-ink-soft">
                    Like what you read? Create a free account to save your own characters and generate as many texts as you like.
                </p>
                <a href="{{ route('register') }}"
                class="inline-flex items-center justify-center rounded-full bg-seal px-8 py-3.5 font-semibold text-white shadow-[0_10px_22px_-10px_rgba(192,57,43,0.55)] transition hover:-translate-y-0.5 hover:bg-seal-deep">
                    免费注册 · Sign up to get more texts
                </a>
            </div>
        @endif

        @if(!empty($story))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('try-it-banner')
                    ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        </script>
        @endif

        {{-- Footer --}}
        <footer class="absolute bottom-6 text-xs tracking-[0.12em] text-ink-soft">Created by John Gu</footer>
    </main>

    <script>
        const characters_lists = @json($vocabLists);
        function fillCharactersTextBox(e) {
            const vocab_list_name = event.currentTarget.getAttribute('data-characters-list-name');
            const characters = characters_lists.find(l => l.vocab_list_name === vocab_list_name)?.characters ?? [];
            document.getElementById('characters').value = characters.join(' ');
            document.getElementById('characters-textbox-count').textContent = `Currently using: ${characters.length} characters`;

        }
    </script>
</body>
</html>