@extends('layouts.app')

@section('title', 'Google Ads')

@section('content')
<div x-data="{ addOpen: false, importOpen: false, campOpen: false }">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 fb-fade-up">
        <div>
            <h1 class="fb-title">Google Ads Insights 🔎</h1>
            <p class="fb-subtitle">Impressions, klik, biaya, konversi & ROAS kampanye iklan Google Anda.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button @click="campOpen = true" class="fb-btn fb-btn-ghost fb-btn-sm"><i data-lucide="flag" class="w-4 h-4"></i> Campaign</button>
            <button @click="importOpen = true" class="fb-btn fb-btn-ghost fb-btn-sm"><i data-lucide="upload" class="w-4 h-4"></i> Import CSV</button>
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
            <label class="fb-label">Campaign</label>
            <select name="campaign_id" class="fb-select">
                <option value="">Semua campaign</option>
                @foreach($campaigns as $c)
                <option value="{{ $c->id }}" @selected((string) $selectedCampaign === (string) $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="fb-btn fb-btn-primary fb-btn-sm">Terapkan</button>
    </form>

    <!-- KPI -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-7 gap-3 mb-5">
        @php
            $kpis = [
                ['Impressions', $totals['impressions'], 'eye', 'bg-blue-50 text-ios-blue', 'int'],
                ['Klik', $totals['clicks'], 'mouse-pointer-click', 'bg-orange-50 text-ios-orange', 'int'],
                ['CTR', $totals['ctr'], 'percent', 'bg-teal-50 text-teal-600', 'percent'],
                ['CPC', $totals['cpc'], 'coins', 'bg-yellow-50 text-yellow-600', 'currency'],
                ['Biaya', $totals['cost'], 'wallet', 'bg-purple-50 text-ios-purple', 'currency'],
                ['Konversi', $totals['conversions'], 'target', 'bg-green-50 text-green-600', 'int'],
                ['ROAS', $totals['roas'], 'trending-up', 'bg-pink-50 text-pink-500', 'plain'],
            ];
        @endphp
        @foreach($kpis as $i => [$label, $value, $icon, $iconBg, $format])
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:{{ 0.05 + $i * 0.03 }}s">
            <div class="fb-kpi-icon {{ $iconBg }} mb-2"><i data-lucide="{{ $icon }}" class="w-5 h-5"></i></div>
            <div class="fb-kpi-value fb-num text-[19px]">
                @if($format === 'currency')Rp {{ number_format($value) }}@elseif($format === 'percent'){{ number_format($value, 2) }}%@elseif($format === 'plain'){{ $value }}x@else{{ number_format($value) }}@endif
            </div>
            <div class="fb-kpi-label">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    <!-- Charts row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
        <div class="fb-card fb-card-pad lg:col-span-2 fb-fade-up" style="animation-delay:0.12s">
            <div class="fb-card-title mb-1">Tren Impressions & Klik</div>
            <div class="fb-card-sub mb-4">{{ \Illuminate\Support\Carbon::parse($from)->translatedFormat('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($to)->translatedFormat('d M Y') }}</div>
            <div class="h-[270px]"><canvas id="gads-trend"></canvas></div>
        </div>
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.16s">
            <div class="fb-card-title mb-1">Biaya per Campaign</div>
            <div class="fb-card-sub mb-4">Distribusi spend</div>
            <div class="h-[220px]"><canvas id="gads-donut"></canvas></div>
            <div class="mt-2 text-center text-[12.5px] text-ios-gray">Total biaya: <b class="text-ios-label">Rp {{ number_format($totals['cost']) }}</b></div>
        </div>
    </div>

    <!-- Charts row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.2s">
            <div class="fb-card-title mb-1">Konversi & ROAS</div>
            <div class="fb-card-sub mb-4">Performa harian</div>
            <div class="h-[220px]"><canvas id="gads-conv"></canvas></div>
        </div>
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.24s">
            <div class="fb-card-title mb-1">Klik per Campaign</div>
            <div class="fb-card-sub mb-4">Perbandingan campaign</div>
            <div class="h-[220px]"><canvas id="gads-campaign"></canvas></div>
        </div>
    </div>

    <!-- Tabel -->
    <div class="fb-table-wrap fb-fade-up" style="animation-delay:0.28s">
        <div class="flex items-center justify-between px-5 py-4 bg-white">
            <div class="fb-card-title">Data Harian Campaign</div>
            <span class="fb-chip">{{ count($rows) }} baris data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="fb-table">
                <thead><tr><th>Tanggal</th><th>Campaign</th><th>Impressions</th><th>Klik</th><th>CTR</th><th>CPC</th><th>Biaya</th><th>Konversi</th><th>ROAS</th><th></th></tr></thead>
                <tbody>
                @forelse($rows->reverse() as $r)
                <tr>
                    <td class="font-semibold">{{ $r->date->translatedFormat('d M Y') }}</td>
                    <td>{{ optional($r->campaign)->name ?? '—' }}</td>
                    <td class="fb-num">{{ number_format($r->impressions) }}</td>
                    <td class="fb-num font-semibold text-ios-blue">{{ number_format($r->clicks) }}</td>
                    <td class="fb-num">{{ $r->ctr !== null ? number_format($r->ctr, 2) . '%' : '—' }}</td>
                    <td class="fb-num">{{ $r->cpc !== null ? 'Rp ' . number_format($r->cpc) : '—' }}</td>
                    <td class="fb-num">Rp {{ number_format($r->cost) }}</td>
                    <td class="fb-num">{{ number_format($r->conversions) }}</td>
                    <td class="fb-num {{ ($r->roas ?? 0) >= 2 ? 'text-green-600 font-bold' : '' }}">{{ $r->roas !== null ? number_format($r->roas, 2) . 'x' : '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('tools.google-ads.destroy', $r) }}" onsubmit="return confirm('Hapus data ini?');">
                            @csrf @method('DELETE')
                            <button class="fb-editor-tool text-ios-gray"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10"><div class="fb-empty !py-12"><div class="icon">📊</div><p>Belum ada data. Tambahkan campaign lalu input/import insight harian.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Campaign list -->
    <div class="fb-card fb-card-pad mt-5 fb-fade-up" style="animation-delay:0.32s">
        <div class="fb-card-title mb-3">Daftar Campaign</div>
        @if($campaigns->count())
        <div class="flex flex-wrap gap-2">
            @foreach($campaigns as $c)
            <div class="fb-chip">
                <span class="fb-dot {{ $c->status === 'active' ? 'bg-green-500' : ($c->status === 'paused' ? 'bg-orange-400' : 'bg-red-400') }}"></span>
                {{ $c->name }}
                <form method="POST" action="{{ route('tools.google-ads.campaigns.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus campaign & datanya?');">
                    @csrf @method('DELETE')
                    <button class="text-ios-gray hover:text-ios-red transition"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
                </form>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-[13.5px] text-ios-gray">Belum ada campaign. Tambahkan lewat tombol <b>Campaign</b>.</p>
        @endif
    </div>

    <!-- Modal: Campaign -->
    <template x-teleport="body">
    <div x-show="campOpen" x-cloak class="fb-modal-center" @click.self="campOpen = false">
        <div class="fb-modal-card">
            <div class="fb-card-title mb-4">Tambah Campaign</div>
            <form method="POST" action="{{ route('tools.google-ads.campaigns.store') }}">
                @csrf
                <div class="fb-field"><label class="fb-label">Nama Campaign</label><input type="text" name="name" class="fb-input" placeholder="cth: Search - Brand" required></div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="fb-field"><label class="fb-label">Campaign ID</label><input type="text" name="campaign_id" class="fb-input"></div>
                    <div class="fb-field"><label class="fb-label">Status</label>
                        <select name="status" class="fb-select">
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="removed">Removed</option>
                        </select>
                    </div>
                </div>
                <button class="fb-btn fb-btn-primary w-full">Simpan Campaign</button>
            </form>
        </div>
    </div>
    </template>

    <!-- Modal: Input manual -->
    <template x-teleport="body">
    <div x-show="addOpen" x-cloak class="fb-modal-center" @click.self="addOpen = false">
        <div class="fb-modal-card">
            <div class="fb-card-title mb-4">Input Insight Manual</div>
            <form method="POST" action="{{ route('tools.google-ads.store') }}">
                @csrf
                <div class="fb-field">
                    <label class="fb-label">Campaign</label>
                    <select name="campaign_id" class="fb-select" required>
                        @foreach($campaigns as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="fb-field"><label class="fb-label">Tanggal</label><input type="date" name="date" class="fb-input" value="{{ date('Y-m-d') }}" required></div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="fb-field"><label class="fb-label">Impressions</label><input type="number" name="impressions" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Klik</label><input type="number" name="clicks" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Biaya (Rp)</label><input type="number" name="cost" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Konversi</label><input type="number" name="conversions" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field col-span-2"><label class="fb-label">Nilai Konversi (Rp)</label><input type="number" name="conversion_value" class="fb-input" min="0" value="0"></div>
                </div>
                <button class="fb-btn fb-btn-primary w-full">Simpan</button>
            </form>
        </div>
    </div>
    </template>

    <!-- Modal: Import CSV -->
    <template x-teleport="body">
    <div x-show="importOpen" x-cloak class="fb-modal-center" @click.self="importOpen = false">
        <div class="fb-modal-card">
            <div class="fb-card-title mb-1">Import CSV</div>
            <p class="fb-card-sub mb-4">Kolom: date, impressions, clicks, cost, conversions, conversion_value</p>
            <form method="POST" action="{{ route('tools.google-ads.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="fb-field">
                    <label class="fb-label">Campaign</label>
                    <select name="campaign_id" class="fb-select" required>
                        @foreach($campaigns as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="fb-field"><label class="fb-label">File CSV</label><input type="file" name="csv" accept=".csv,.txt" class="fb-input" required></div>
                <button class="fb-btn fb-btn-primary w-full">Import</button>
            </form>
        </div>
    </div>
    </template>
</div>
@endsection

@section('scripts')
<script>
    window.PAGE_CHARTS = [
        {
            id: 'gads-trend',
            config: {
                type: 'line',
                labels: @json($impressionsSeries['labels']),
                datasets: [
                    { label: 'Impressions', data: @json($impressionsSeries['values']), color: '#0A84FF', fill: true },
                    { label: 'Klik', data: @json($clicksSeries['values']), color: '#FF9500', fill: false },
                ],
                legend: true,
            },
        },
        {
            id: 'gads-donut',
            config: { type: 'doughnut', labels: @json($byCampaign['labels']), datasets: [{ data: @json($byCampaign['values']) }], legend: true, tooltip: 'currency', symbol: 'Rp ' },
        },
        {
            id: 'gads-conv',
            config: {
                type: 'bar',
                labels: @json($conversionsSeries['labels']),
                datasets: [
                    { label: 'Konversi', data: @json($conversionsSeries['values']), color: '#34C759' },
                    { label: 'ROAS (x)', data: @json($roasSeries['values']), color: '#AF52DE' },
                ],
                legend: true,
            },
        },
        {
            id: 'gads-campaign',
            config: { type: 'bar', labels: @json($campaignClicks['labels']), datasets: [{ label: 'Klik', data: @json($campaignClicks['values']), color: '#5AC8FA' }], legend: false },
        },
    ];
</script>
@endsection