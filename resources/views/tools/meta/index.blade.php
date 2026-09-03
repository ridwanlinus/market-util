@extends('layouts.app')

@section('title', 'Meta Insights')

@section('content')
<div x-data="{ addOpen: false, importOpen: false, syncOpen: false, pageOpen: false, connectOpen: false }">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 fb-fade-up">
        <div>
            <h1 class="fb-title">Meta Insights 📊</h1>
            <p class="fb-subtitle">Impressions, reach, engagement & performa halaman Meta / Meta Ads Anda.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button @click="pageOpen = true" class="fb-btn fb-btn-ghost fb-btn-sm"><i data-lucide="plus" class="w-4 h-4"></i> Halaman</button>
            <button @click="importOpen = true" class="fb-btn fb-btn-ghost fb-btn-sm"><i data-lucide="upload" class="w-4 h-4"></i> Import CSV</button>
            <button @click="syncOpen = true" class="fb-btn fb-btn-primary fb-btn-sm"><i data-lucide="refresh-cw" class="w-4 h-4"></i> Sinkron API</button>
            <button @click="addOpen = true" class="fb-btn fb-btn-green fb-btn-sm"><i data-lucide="plus" class="w-4 h-4"></i> Input Manual</button>
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" class="fb-card fb-card-pad !py-4 mb-5 flex flex-wrap items-end gap-3 fb-fade-up" style="animation-delay:0.04s">
        <div class="fb-field !mb-0">
            <label class="fb-label">Dari</label>
            <input type="date" name="from" value="{{ $from }}" class="fb-input">
        </div>
        <div class="fb-field !mb-0">
            <label class="fb-label">Sampai</label>
            <input type="date" name="to" value="{{ $to }}" class="fb-input">
        </div>
        <div class="fb-field !mb-0 min-w-[200px]">
            <label class="fb-label">Halaman</label>
            <select name="page_id" class="fb-select">
                <option value="">Semua halaman</option>
                @foreach($pages as $p)
                <option value="{{ $p->id }}" @selected((string) $selectedPage === (string) $p->id)>{{ $p->name }} ({{ number_format($p->followers_count) }} followers)</option>
                @endforeach
            </select>
        </div>
        <button class="fb-btn fb-btn-primary fb-btn-sm">Terapkan</button>
    </form>

    <!-- KPI -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-5">
        @php
            $kpis = [
                ['Impressions', $totals['impressions'], 'eye', 'bg-blue-50 text-ios-blue', 'int', $impressionsPeriod['delta']],
                ['Reach', $totals['reach'], 'users', 'bg-teal-50 text-teal-600', 'int', null],
                ['Engagement', $totals['engagement'], 'heart', 'bg-pink-50 text-pink-500', 'int', $period['delta']],
                ['CTR', $totals['ctr'], 'mouse-pointer-click', 'bg-orange-50 text-ios-orange', 'percent', null],
                ['Spend', $totals['spend'], 'wallet', 'bg-purple-50 text-ios-purple', 'currency', $spendPeriod['delta']],
            ];
        @endphp
        @foreach($kpis as $i => [$label, $value, $icon, $iconBg, $format, $delta])
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:{{ 0.06 + $i * 0.04 }}s">
            <div class="fb-kpi">
                <div class="fb-kpi-icon {{ $iconBg }}"><i data-lucide="{{ $icon }}" class="w-5 h-5"></i></div>
                <div class="min-w-0">
                    <div class="fb-kpi-value fb-num text-[20px]">
                        <span data-count="{{ $value }}" data-format="{{ $format }}" data-symbol="Rp ">{{ $format === 'currency' ? 'Rp ' . number_format($value) : number_format($value) }}</span>
                    </div>
                    <div class="fb-kpi-label">{{ $label }}</div>
                </div>
            </div>
            @if($delta !== null)
            <div class="mt-2 pt-2 border-t border-black/5">
                <span class="fb-delta {{ $delta >= 0 ? 'fb-delta-up' : 'fb-delta-down' }}">{{ $delta >= 0 ? '▲' : '▼' }} {{ abs($delta) }}% vs 30 hari sblm</span>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
        <div class="fb-card fb-card-pad lg:col-span-2 fb-fade-up" style="animation-delay:0.14s">
            <div class="fb-card-title mb-1">Tren Impressions & Reach</div>
            <div class="fb-card-sub mb-4">{{ \Illuminate\Support\Carbon::parse($from)->translatedFormat('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($to)->translatedFormat('d M Y') }}</div>
            <div class="h-[280px]"><canvas id="meta-trend"></canvas></div>
        </div>
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.18s">
            <div class="fb-card-title mb-1">Interaksi</div>
            <div class="fb-card-sub mb-4">Likes · Comments · Shares · Saves</div>
            <div class="h-[240px]"><canvas id="meta-donut"></canvas></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.22s">
            <div class="fb-card-title mb-1">Spend Harian</div>
            <div class="fb-card-sub mb-4">Budget iklan per hari</div>
            <div class="h-[220px]"><canvas id="meta-spend"></canvas></div>
        </div>
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.26s">
            <div class="fb-card-title mb-1">Engagement per Halaman</div>
            <div class="fb-card-sub mb-4">Kontribusi tiap halaman</div>
            <div class="h-[220px]"><canvas id="meta-by-page"></canvas></div>
        </div>
    </div>

    <!-- Tabel -->
    <div class="fb-table-wrap fb-fade-up" style="animation-delay:0.3s">
        <div class="flex items-center justify-between px-5 py-4 bg-white">
            <div class="fb-card-title">Data Harian Meta</div>
            <span class="fb-chip">{{ count($rows) }} hari data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="fb-table">
                <thead><tr><th>Tanggal</th><th>Halaman</th><th>Impressions</th><th>Reach</th><th>Engagement</th><th>Likes</th><th>Comments</th><th>Shares</th><th>CTR</th><th>Spend</th><th></th></tr></thead>
                <tbody>
                @forelse($rows->reverse() as $r)
                <tr>
                    <td class="font-semibold">{{ $r->date->translatedFormat('d M Y') }}</td>
                    <td>{{ optional($r->metaPage)->name ?? '—' }}</td>
                    <td class="fb-num">{{ number_format($r->impressions) }}</td>
                    <td class="fb-num">{{ number_format($r->reach) }}</td>
                    <td class="fb-num font-semibold text-ios-blue">{{ number_format($r->engagement) }}</td>
                    <td class="fb-num">{{ number_format($r->likes) }}</td>
                    <td class="fb-num">{{ number_format($r->comments) }}</td>
                    <td class="fb-num">{{ number_format($r->shares) }}</td>
                    <td class="fb-num">{{ $r->ctr !== null ? number_format($r->ctr, 2) . '%' : '—' }}</td>
                    <td class="fb-num">{{ $r->spend ? 'Rp ' . number_format($r->spend) : '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('tools.meta.destroy', $r) }}" onsubmit="return confirm('Hapus data ini?');">
                            @csrf @method('DELETE')
                            <button class="fb-editor-tool text-ios-gray"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11"><div class="fb-empty !py-12"><div class="icon">📊</div><p>Belum ada data. Tambahkan lewat input manual, import CSV, atau sinkronisasi API.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== Modal: Input manual ===== -->
    <template x-teleport="body">
    <div x-show="addOpen" x-cloak class="fb-modal-center" @click.self="addOpen = false">
        <div class="fb-modal-card">
            <div class="fb-card-title mb-4">Input Insight Manual</div>
            <form method="POST" action="{{ route('tools.meta.store') }}">
                @csrf
                <div class="fb-field">
                    <label class="fb-label">Halaman</label>
                    <select name="meta_page_id" class="fb-select" required>
                        @foreach($pages as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div class="fb-field"><label class="fb-label">Tanggal</label><input type="date" name="date" class="fb-input" value="{{ date('Y-m-d') }}" required></div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="fb-field"><label class="fb-label">Impressions</label><input type="number" name="impressions" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Reach</label><input type="number" name="reach" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Engagement</label><input type="number" name="engagement" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Clicks</label><input type="number" name="clicks" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Likes</label><input type="number" name="likes" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Comments</label><input type="number" name="comments" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Shares</label><input type="number" name="shares" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Spend (Rp)</label><input type="number" name="spend" class="fb-input" min="0" value="0"></div>
                </div>
                <button class="fb-btn fb-btn-primary w-full">Simpan</button>
            </form>
        </div>
    </div>
    </template>

    <!-- ===== Modal: Import CSV ===== -->
    <template x-teleport="body">
    <div x-show="importOpen" x-cloak class="fb-modal-center" @click.self="importOpen = false">
        <div class="fb-modal-card">
            <div class="fb-card-title mb-1">Import CSV</div>
            <p class="fb-card-sub mb-4">Kolom: date, impressions, reach, engagement, likes, comments, shares, saves, clicks, spend</p>
            <form method="POST" action="{{ route('tools.meta.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="fb-field">
                    <label class="fb-label">Halaman</label>
                    <select name="meta_page_id" class="fb-select" required>
                        @foreach($pages as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div class="fb-field"><label class="fb-label">File CSV</label><input type="file" name="csv" accept=".csv,.txt" class="fb-input" required></div>
                <button class="fb-btn fb-btn-primary w-full">Import</button>
            </form>
        </div>
    </div>
    </template>

    <!-- ===== Modal: Sync API ===== -->
    <template x-teleport="body">
    <div x-show="syncOpen" x-cloak class="fb-modal-center" @click.self="syncOpen = false">
        <div class="fb-modal-card">
            <div class="fb-card-title mb-1">Sinkronisasi Meta Graph API</div>
            <p class="fb-card-sub mb-4">Ambil insight harian langsung dari Facebook. Pastikan Access Token sudah diatur di Settings.</p>
            <form method="POST" action="{{ route('tools.meta.sync') }}">
                @csrf
                <div class="fb-field">
                    <label class="fb-label">Halaman</label>
                    <select name="meta_page_id" class="fb-select" required>
                        @foreach($pages as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="fb-field"><label class="fb-label">Dari</label><input type="date" name="from" class="fb-input" value="{{ now()->subDays(29)->format('Y-m-d') }}" required></div>
                    <div class="fb-field"><label class="fb-label">Sampai</label><input type="date" name="to" class="fb-input" value="{{ now()->format('Y-m-d') }}" required></div>
                </div>
                <button class="fb-btn fb-btn-primary w-full">Sinkronkan</button>
            </form>
        </div>
    </div>
    </template>

    <!-- ===== Modal: Tambah halaman ===== -->
    <template x-teleport="body">
    <div x-show="pageOpen" x-cloak class="fb-modal-center" @click.self="pageOpen = false">
        <div class="fb-modal-card">
            <div class="fb-card-title mb-4">Tambah Halaman Meta</div>
            <form method="POST" action="{{ route('tools.meta.pages.store') }}">
                @csrf
                <div class="fb-field"><label class="fb-label">Nama Halaman</label><input type="text" name="name" class="fb-input" placeholder="cth: Toko Official" required></div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="fb-field"><label class="fb-label">Page ID (opsional)</label><input type="text" name="page_id" class="fb-input"></div>
                    <div class="fb-field"><label class="fb-label">Followers</label><input type="number" name="followers_count" class="fb-input" min="0" value="0"></div>
                </div>
                <div class="fb-field"><label class="fb-label">Access Token (opsional)</label><input type="password" name="access_token" class="fb-input"></div>
                <button class="fb-btn fb-btn-primary w-full">Simpan Halaman</button>
            </form>
            <div class="flex items-center gap-3 my-4"><div class="flex-1 h-px bg-black/5"></div><span class="text-[11px] text-ios-gray">atau</span><div class="flex-1 h-px bg-black/5"></div></div>
            <form method="POST" action="{{ route('tools.meta.pages.connect') }}">
                @csrf
                <div class="fb-field"><label class="fb-label">Auto-connect dengan token global (Settings)</label>
                    <input type="password" name="access_token" class="fb-input" placeholder="Token dari Settings">
                </div>
                <button class="fb-btn fb-btn-ghost w-full">Hubungkan Halaman Saya</button>
            </form>
            @if($pages->count())
            <div class="mt-4 pt-3 border-t border-black/5 space-y-2">
                @foreach($pages as $p)
                <div class="flex items-center justify-between text-[13px]">
                    <span class="font-medium truncate">{{ $p->name }}</span>
                    <form method="POST" action="{{ route('tools.meta.pages.destroy', $p) }}" onsubmit="return confirm('Hapus halaman ini?');">
                        @csrf @method('DELETE')
                        <button class="text-ios-red text-[12px] font-semibold">Hapus</button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    </template>
</div>
@endsection

@section('scripts')
<script>
    window.PAGE_CHARTS = [
        {
            id: 'meta-trend',
            config: {
                type: 'line',
                labels: @json($impressionsSeries['labels']),
                datasets: [
                    { label: 'Impressions', data: @json($impressionsSeries['values']), color: '#0A84FF', fill: true },
                    { label: 'Reach', data: @json($reachSeries['values']), color: '#5AC8FA', fill: false },
                ],
                legend: true,
            },
        },
        {
            id: 'meta-donut',
            config: { type: 'doughnut', labels: @json($interactionDonut['labels']), datasets: [{ data: @json($interactionDonut['values']) }], legend: true },
        },
        {
            id: 'meta-spend',
            config: { type: 'bar', labels: @json($spendSeries['labels']), datasets: [{ label: 'Spend', data: @json($spendSeries['values']), color: '#AF52DE' }], legend: false, tooltip: 'currency', symbol: 'Rp ' },
        },
        {
            id: 'meta-by-page',
            config: { type: 'doughnut', labels: @json($byPage['labels']), datasets: [{ data: @json($byPage['values']) }], legend: true },
        },
    ];
</script>
@endsection