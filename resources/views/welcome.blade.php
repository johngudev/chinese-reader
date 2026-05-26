<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>识字 · Chinese Reader</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Noto+Serif+SC:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

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

            <p class="mt-4 text-sm uppercase tracking-[0.36em] text-ink-soft">Chinese Reader</p>

            <p class="mx-auto mt-7 max-w-md text-lg leading-relaxed text-ink-soft">
                Stories woven entirely from the characters you already know —
                and a single tap to take on the next one.
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

                        <textarea name="characters" id="characters" rows="10"
                            class="mt-3 block w-full rounded-lg border-gray-300 font-serifsc text-lg leading-relaxed focus:border-seal focus:ring-seal"
                            placeholder="你好我是中国人…">{{ implode(' ', $characters) }}</textarea>

                        <div class="my-4 flex items-center justify-center md:justify-between">
                            <span class="text-sm text-gray-500 hidden md:inline">
                                Currently using: {{ count($characters) }} characters
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
                <article class="overflow-y-auto px-8 py-10 sm:px-12 sm:py-14 bg-white">
                        <p class="whitespace-pre-line text-2xl leading-loose tracking-wide text-gray-900"
                        style="font-family: 'Noto Serif SC', 'Songti SC', serif;">
                            {{ $story }}
                        </p>
                </article>
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
</body>
</html>