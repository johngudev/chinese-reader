<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Retention · Dashboard</title>
    <meta name="robots" content="noindex">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Noto+Serif+SC:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head>

<body class="antialiased">
    <main class="relative min-h-screen overflow-hidden bg-gradient-to-b from-[#fffdf8] via-paper to-paper2 px-6 py-12 text-ink sm:px-8">

        {{-- Faded background character --}}
        <div aria-hidden="true"
             class="pointer-events-none fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 select-none font-serifsc text-[min(60vw,40rem)] font-bold leading-none text-ink opacity-[0.025]">
            留
        </div>

        <div class="relative z-10 mx-auto max-w-4xl">

            <a href="{{ url('/') }}"
               class="inline-flex items-center gap-2 text-sm text-ink-soft transition hover:text-seal">
                <span aria-hidden="true">←</span> 识字 · Let's Read Chinese!
            </a>

            <header class="mt-8">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-seal">留存 · Retention</p>
                <h1 class="relative mt-2 inline-block font-serifsc text-3xl font-bold sm:text-4xl">
                    Drop-off Curve
                    <span class="absolute -right-4 top-1 h-2.5 w-2.5 rotate-45 rounded-sm bg-seal"></span>
                </h1>
                <p class="mt-3 text-sm text-ink-soft">
                    % of users whose latest generation is on or after day <em>n</em> since signup.
                </p>
            </header>

            {{-- Stat cards --}}
            <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-line bg-white/70 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.18em] text-ink-soft">Registered users</p>
                    <p class="mt-1 font-serifsc text-3xl font-bold">{{ number_format($totalUsers) }}</p>
                </div>
                <div class="rounded-2xl border border-line bg-white/70 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.18em] text-ink-soft">Ever generated</p>
                    <p class="mt-1 font-serifsc text-3xl font-bold">{{ $activationPct }}%</p>
                </div>
                <div class="rounded-2xl border border-line bg-white/70 p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.18em] text-ink-soft">Total generations</p>
                    <p class="mt-1 font-serifsc text-3xl font-bold">{{ number_format($totalGenerations) }}</p>
                </div>
            </div>

            {{-- Chart --}}
            <div class="mt-6 rounded-2xl border border-line bg-white/80 p-6 shadow-sm">
                @if (count($labels) > 1)
                    <div class="h-80 sm:h-96">
                        <canvas id="retention-curve-chart"></canvas>
                    </div>
                @else
                    <p class="py-16 text-center text-sm text-ink-soft">
                        Not enough history yet — the curve appears once accounts are a few days old.
                    </p>
                @endif
            </div>

            <p class="mt-4 text-xs leading-relaxed text-ink-soft">
                Hover a point to see how many users were old enough to count at that day.
                The right edge is computed on only your oldest accounts — read it loosely.
            </p>

        </div>
    </main>

    @if (count($labels) > 1)
    <script>
        const labels   = @json($labels);
        const percents = @json($percents);
        const eligible = @json($eligibleCounts);

        new Chart(document.getElementById('retention-curve-chart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Still generating',
                    data: percents,
                    borderColor: '#C0392B',
                    backgroundColor: 'rgba(192, 57, 43, 0.08)',
                    fill: true,
                    borderWidth: 2,
                    pointRadius: labels.length > 60 ? 0 : 2.5,
                    pointBackgroundColor: '#C0392B',
                    tension: 0.2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: items => `Day ${items[0].label}`,
                            label: item => `${item.parsed.y}% still generating`,
                            afterLabel: item => `${eligible[item.dataIndex]} users eligible`,
                        },
                    },
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        ticks: { callback: v => v + '%', color: '#8a837b' },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    },
                    x: {
                        title: { display: true, text: 'Days since signup', color: '#8a837b' },
                        ticks: { color: '#8a837b', maxTicksLimit: 12, maxRotation: 0 },
                        grid: { display: false },
                    },
                },
            },
        });
    </script>
    @endif
</body>
</html>