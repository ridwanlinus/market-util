@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 fb-fade-up">
        <div>
            <h1 class="fb-title">Halo, {{ auth()->user()->name }} 👋</h1>
            <p class="fb-subtitle">{{ now()->translatedFormat('l, d F Y') }} · Ringkasan performa {{ optional(auth()->user()->company)->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tools.content.create') }}" class="fb-btn fb-btn-primary fb-btn-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> Konten Baru
            </a>
        </div>
    </div>

    <!-- ===== KPI ROW ===== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.03s">
            <div class="fb-kpi">
                <div class="fb-kpi-icon bg-blue-50 text-ios-blue"><i data-lucide="palette" class="w-5 h-5"></i></div>
                <div>
                    <div class="fb-kpi-value fb-count" data-count="{{ $contentStats->total ?? 0 }}" data-format="int">{{ $contentStats->total ?? 0 }}</div>
                    <div class="fb-kpi-label">Total Konten</div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-black/5 text-[12.5px] text-ios-gray flex gap-3">
                <span><b class="text-green-600">{{ $contentStats->approved ?? 0 }}</b> approved</span>
                <span><b class="text-orange-500">{{ $contentStats->pending ?? 0 }}</b> pending</span>
            </div>
        </div>

        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.07s">
            <div class="fb-kpi">
                <div class="fb-kpi-icon bg-pink-50 text-pink-500"><i data-lucide="heart-handshake" class="w-5 h-5"></i></div>
                <div>
                    <div class="fb-kpi-value">{{ $avgEr }}<span class="text-[16px] text-ios-gray">%</span></div>
                    <div class="fb-kpi-label">Rata-rata Engagement Rate</div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-black/5 text-[12.5px] text-ios-gray">
                @if($posts->count() > 0)
                Benchmark industri: <b>1.5–3%</b> = rata-rata
                @else
                Belum ada data post Meta
                @endif
            </div>
        </div>

        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.11s">
            <div class="fb-kpi">
                <div class="fb-kpi-icon bg-orange-50 text-ios-orange"><i data-lucide="mouse-pointer-click" class="w-5 h-5"></i></div>
                <div>
                    <div class="fb-kpi-value">{{ number_format($gaTotals['clicks'], 0, ',', '.') }}</div>
                    <div class="fb-kpi-label">Klik Google Ads</div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-black/5 text-[12.5px] text-ios-gray">
                ROAS: <b class="text-green-600">{{ $roas }}x</b> · Konversi: {{ number_format($gaTotals['conversions'], 0, ',', '.') }}
            </div>
        </div>

        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.15s">
            <div class="fb-kpi">
                <div class="fb-kpi-icon bg-purple-50 text-ios-purple"><i data-lucide="users" class="w-5 h-5"></i></div>
                <div>
                    <div class="fb-kpi-value">{{ number_format($analyticsTotals['users'], 0, ',', '.') }}</div>
                    <div class="fb-kpi-label">Pengunjung Website</div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-black/5 text-[12.5px] text-ios-gray">
                {{ number_format($analyticsTotals['pageviews'], 0, ',', '.') }} pageviews · {{ number_format($analyticsTotals['sessions'], 0, ',', '.') }} sesi
            </div>
        </div>
    </div>

    <!-- ===== TREND CHARTS ===== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="fb-card fb-card-pad lg:col-span-2 fb-fade-up" style="animation-delay:0.18s">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="fb-card-title">Tren Meta Insights</div>
                    <div class="fb-card-sub">Impressions 30 hari terakhir @if($metaPeriod['delta'] !== null)<span class="fb-delta {{ $metaPeriod['delta'] >= 0 ? 'fb-delta-up' : 'fb-delta-down' }}">· {{ $metaPeriod['delta'] >= 0 ? '▲' : '▼' }} {{ abs($metaPeriod['delta']) }}%</span>@endif</div>
                </div>
                <a href="{{ route('tools.meta.index') }}" class="text-[13px] font-semibold text-ios-blue">Detail →</a>
            </div>
            <div class="h-[260px]">
                <canvas id="dash-meta"></canvas>
            </div>
        </div>

        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.22s">
            <div class="fb-card-title mb-1">Distribusi Interaksi</div>
            <div class="fb-card-sub mb-4">Meta — Likes, Comments, Shares, Saves</div>
            <div class="h-[220px]">
                <canvas id="dash-donut"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.25s">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="fb-card-title">Google Ads — Klik</div>
                    <div class="fb-card-sub">30 hari terakhir @if($gaPeriod['delta'] !== null)<span class="fb-delta {{ $gaPeriod['delta'] >= 0 ? 'fb-delta-up' : 'fb-delta-down' }}">· {{ $gaPeriod['delta'] >= 0 ? '▲' : '▼' }} {{ abs($gaPeriod['delta']) }}%</span>@endif</div>
                </div>
                <a href="{{ route('tools.google-ads.index') }}" class="text-[13px] font-semibold text-ios-blue">Detail →</a>
            </div>
            <div class="h-[220px]">
                <canvas id="dash-ads"></canvas>
            </div>
        </div>

        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.28s">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="fb-card-title">Traffic Website</div>
                    <div class="fb-card-sub">Users 30 hari terakhir @if($analyticsPeriod['delta'] !== null)<span class="fb-delta {{ $analyticsPeriod['delta'] >= 0 ? 'fb-delta-up' : 'fb-delta-down' }}">· {{ $analyticsPeriod['delta'] >= 0 ? '▲' : '▼' }} {{ abs($analyticsPeriod['delta']) }}%</span>@endif</div>
                </div>
                <a href="{{ route('tools.analytics.index') }}" class="text-[13px] font-semibold text-ios-blue">Detail →</a>
            </div>
            <div class="h-[220px]">
                <canvas id="dash-ga"></canvas>
            </div>
        </div>
    </div>

    <!-- ===== ER TREND + KONTEN TERBARU ===== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="fb-card fb-card-pad lg:col-span-1 fb-fade-up" style="animation-delay:0.31s">
            <div class="fb-card-title mb-1">Engagement Rate per Konten</div>
            <div class="fb-card-sub mb-4">Berdasarkan post Meta terbaru</div>
            <div class="h-[240px]">
                <canvas id="dash-er"></canvas>
            </div>
            <div class="mt-4 pt-4 border-t border-black/5">
                <a href="{{ route('tools.engagement.index') }}" class="fb-btn fb-btn-ghost w-full">Buka Kalkulator ER</a>
            </div>
        </div>

        <div class="fb-card fb-card-pad lg:col-span-2 fb-fade-up" style="animation-delay:0.34s">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="fb-card-title">Konten Terbaru</div>
                    <div class="fb-card-sub">Status approval produksi konten</div>
                </div>
                <a href="{{ route('tools.content.index') }}" class="text-[13px] font-semibold text-ios-blue">Semua →</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @forelse($contents as $c)
                <a href="{{ route('tools.content.show', $c) }}" class="group rounded-2xl overflow-hidden bg-ios-gray relative block">
                    @if($c->coverUrl())
                    <img src="{{ $c->coverUrl() }}" class="w-full aspect-[4/5] object-cover group-hover:scale-105 transition duration-300" alt="">
                    @else
                    <div class="w-full aspect-[4/5] flex items-center justify-center bg-gradient-to-br from-ios-blue to-ios-purple text-white text-4xl font-bold">{{ strtoupper(substr($c->title,0,1)) }}</div>
                    @endif
                    <div class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black/70 to-transparent">
                        <div class="text-white text-[12.5px] font-semibold truncate">{{ $c->title }}</div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="fb-badge {{ match($c->status) { 'approved' => 'fb-badge-green', 'pending' => 'fb-badge-orange', 'rejected' => 'fb-badge-red', default => 'fb-badge-gray' } }} !bg-white/20 !text-white">{{ $c->statusLabel() }}</span>
                            @if($c->type === 'carousel')<span class="text-white/80 text-[11px]"><i data-lucide="images" class="w-3 h-3 inline"></i> {{ $c->slides_count }}</span>@endif
                        </div>
                    </div>
                </a>
                @empty
                <div class="col-span-full fb-empty">
                    <div class="icon">🎨</div>
                    <div class="font-semibold text-ios-label mb-1">Belum ada konten</div>
                    <p>Mulai produksi konten single image atau carousel 4:5.</p>
                    <a href="{{ route('tools.content.create') }}" class="fb-btn fb-btn-primary fb-btn-sm mt-4">Buat Konten Pertama</a>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ===== KESEGARAN DATA ===== -->
    <div class="mt-6 flex flex-wrap gap-3 fb-fade-up" style="animation-delay:0.4s">
        <span class="fb-chip"><i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Data Meta: {{ $latest['meta'] ? $latest['meta']->translatedFormat('d M Y') : 'belum ada' }}</span>
        <span class="fb-chip"><i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Google Ads: {{ $latest['ads'] ? $latest['ads']->translatedFormat('d M Y') : 'belum ada' }}</span>
        <span class="fb-chip"><i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> GA: {{ $latest['ga'] ? $latest['ga']->translatedFormat('d M Y') : 'belum ada' }}</span>
    </div>
@endsection

@section('scripts')
<script>
    window.PAGE_CHARTS = [
        {
            id: 'dash-meta',
            config: {
                type: 'line',
                labels: @json($metaTrend['labels']),
                datasets: [{ label: 'Impressions', data: @json($metaTrend['values']), fill: true, color: '#0A84FF' }],
                legend: false,
            },
        },
        {
            id: 'dash-donut',
            config: {
                type: 'doughnut',
                labels: ['Likes', 'Comments', 'Shares', 'Saves'],
                datasets: [{ data: {{ Js::from([$posts->sum('likes'), $posts->sum('comments'), $posts->sum('shares'), $posts->sum('saves')]) }} }],
                legend: true,
            },
        },
        {
            id: 'dash-ads',
            config: {
                type: 'bar',
                labels: @json($gaTrend['labels']),
                datasets: [{ label: 'Klik', data: @json($gaTrend['values']), color: '#FF9500' }],
                legend: false,
            },
        },
        {
            id: 'dash-ga',
            config: {
                type: 'line',
                labels: @json($analyticsTrend['labels']),
                datasets: [{ label: 'Users', data: @json($analyticsTrend['values']), fill: true, color: '#AF52DE' }],
                legend: false,
            },
        },
        {
            id: 'dash-er',
            config: {
                type: 'bar',
                labels: @json($erSeries->pluck('label')),
                datasets: [{ label: 'ER %', data: @json($erSeries->pluck('value')), color: '#34C759' }],
                legend: false,
                tooltip: 'percent',
            },
        },
    ];
</script>
@endsection