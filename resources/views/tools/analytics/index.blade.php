@extends('layouts.app')

@section('title', 'Google Analytics')

@section('content')
<div x-data="{ addOpen: false, importOpen: false, propOpen: false }">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 fb-fade-up">
        <div>
            <h1 class="fb-title">Google Analytics 📈</h1>
            <p class="fb-subtitle">Traffic website: pengguna, sesi, pageviews & perilaku pengunjung (GA4).</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button @click="propOpen = true" class="fb-btn fb-btn-ghost fb-btn-sm"><i data-lucide="globe" class="w-4 h-4"></i> Properti</button>
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
            <label class="fb-label">Properti</label>
            <select name="property_id" class="fb-select">
                <option value="">Semua properti</option>
                @foreach($properties as $p)
                <option value="{{ $p->id }}" @selected((string) $selectedProperty === (string) $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="fb-btn fb-btn-primary fb-btn-sm">Terapkan</button>
    </form>

    <!-- KPI -->
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3 mb-5">
        @php
            $kpis = [
                ['Users', $totals['users'], 'users', 'bg-blue-50 text-ios-blue', 'int'],
                ['New Users', $totals['new_users'], 'user-plus', 'bg-teal-50 text-teal-600', 'int'],
                ['Sessions', $totals['sessions'], 'refresh-cw', 'bg-purple-50 text-ios-purple', 'int'],
                ['Pageviews', $totals['pageviews'], 'file-text', 'bg-orange-50 text-ios-orange', 'int'],
                ['Pages/Session', $totals['pages_per_session'], 'layers', 'bg-green-50 text-green-600', 'plain'],
                ['Avg. Duration', $totals['avg_session_duration'], 'timer', 'bg-pink-50 text-pink-500', 'duration'],
                ['Bounce Rate', $totals['bounce_rate'], 'corner-down-left', 'bg-yellow-50 text-yellow-600', 'percent'],
            ];
        @endphp
        @foreach($kpis as $i => [$label, $value, $icon, $iconBg, $format])
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:{{ 0.05 + $i * 0.03 }}s">
            <div class="fb-kpi-icon {{ $iconBg }} mb-2"><i data-lucide="{{ $icon }}" class="w-5 h-5"></i></div>
            <div class="fb-kpi-value fb-num text-[19px]">
                @if($format === 'percent'){{ number_format($value, 2) }}%@elseif($format === 'plain'){{ number_format($value, 2) }}@elseif($format === 'duration'){{ gmdate('i\m s\s', (int) $value) }}@else{{ number_format($value) }}@endif
            </div>
            <div class="fb-kpi-label">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
        <div class="fb-card fb-card-pad lg:col-span-2 fb-fade-up" style="animation-delay:0.14s">
            <div class="fb-card-title mb-1">Tren Traffic</div>
            <div class="fb-card-sub mb-4">{{ \Illuminate\Support\Carbon::parse($from)->translatedFormat('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($to)->translatedFormat('d M Y') }}</div>
            <div class="h-[270px]"><canvas id="ga-trend"></canvas></div>
        </div>
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.18s">
            <div class="fb-card-title mb-1">Sumber Trafik</div>
            <div class="fb-card-sub mb-4">Channel pengunjung</div>
            @if(count($channels))
            <div class="h-[200px]"><canvas id="ga-channels"></canvas></div>
            @else
            <div class="fb-empty !py-14"><div class="icon">🌐</div><p class="text-[13px]">Data channel tersedia setelah input/import insight.</p></div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
        <div class="fb-card fb-card-pad lg:col-span-2 fb-fade-up" style="animation-delay:0.22s">
            <div class="fb-card-title mb-1">New Users & Sessions</div>
            <div class="fb-card-sub mb-4">Perbandingan harian</div>
            <div class="h-[220px]"><canvas id="ga-new"></canvas></div>
        </div>
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.26s">
            <div class="fb-card-title mb-3">Halaman Terpopuler</div>
            @if(count($topPages))
            <div class="space-y-3">
                @foreach(array_slice($topPages, 0, 8) as $pg)
                <div>
                    <div class="flex justify-between text-[13px] mb-1">
                        <span class="font-medium truncate">{{ $pg['page'] }}</span>
                        <b class="fb-num">{{ number_format($pg['views']) }}</b>
                    </div>
                    <div class="fb-bar">
                        <div style="width:{{ max(4, round(($pg['views'] / max($topPages[0]['views'], 1)) * 100)) }}%;background:#0A84FF"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="fb-empty !py-10"><div class="icon">🗺️</div><p class="text-[13px]">Belum ada data halaman.</p></div>
            @endif
        </div>
    </div>

    <!-- Tabel -->
    <div class="fb-table-wrap fb-fade-up" style="animation-delay:0.3s">
        <div class="flex items-center justify-between px-5 py-4 bg-white">
            <div class="fb-card-title">Data Harian Traffic</div>
            <span class="fb-chip">{{ count($rows) }} hari data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="fb-table">
                <thead><tr><th>Tanggal</th><th>Properti</th><th>Users</th><th>New Users</th><th>Sessions</th><th>Pageviews</th><th>Duration</th><th>Bounce</th><th></th></tr></thead>
                <tbody>
                @forelse($rows->reverse() as $r)
                <tr>
                    <td class="font-semibold">{{ $r->date->translatedFormat('d M Y') }}</td>
                    <td>{{ optional($r->property)->name ?? '—' }}</td>
                    <td class="fb-num font-semibold text-ios-blue">{{ number_format($r->users) }}</td>
                    <td class="fb-num">{{ number_format($r->new_users) }}</td>
                    <td class="fb-num">{{ number_format($r->sessions) }}</td>
                    <td class="fb-num">{{ number_format($r->pageviews) }}</td>
                    <td class="fb-num">{{ gmdate('i\m s\s', (int) $r->avg_session_duration) }}</td>
                    <td class="fb-num">{{ $r->bounce_rate !== null ? number_format($r->bounce_rate, 1) . '%' : '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('tools.analytics.destroy', $r) }}" onsubmit="return confirm('Hapus data ini?');">
                            @csrf @method('DELETE')
                            <button class="fb-editor-tool text-ios-gray"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="fb-empty !py-12"><div class="icon">📈</div><p>Belum ada data. Tambahkan properti lalu input/import insight.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Properti list -->
    <div class="fb-card fb-card-pad mt-5 fb-fade-up" style="animation-delay:0.34s">
        <div class="fb-card-title mb-3">Properti GA4</div>
        @if($properties->count())
        <div class="flex flex-wrap gap-2">
            @foreach($properties as $p)
            <div class="fb-chip">
                <i data-lucide="globe" class="w-3.5 h-3.5 text-ios-blue"></i>
                {{ $p->name }} @if($p->website)<span class="text-ios-gray font-normal">· {{ $p->website }}</span>@endif
                <form method="POST" action="{{ route('tools.analytics.properties.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus properti?');">
                    @csrf @method('DELETE')
                    <button class="text-ios-gray hover:text-ios-red transition"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
                </form>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-[13.5px] text-ios-gray">Belum ada properti. Tambahkan lewat tombol <b>Properti</b>.</p>
        @endif
    </div>

    <!-- Modal: Properti -->
    <template x-teleport="body">
    <div x-show="propOpen" x-cloak class="fb-modal-center" @click.self="propOpen = false">
        <div class="fb-modal-card">
            <div class="fb-card-title mb-4">Tambah Properti GA4</div>
            <form method="POST" action="{{ route('tools.analytics.properties.store') }}">
                @csrf
                <div class="fb-field"><label class="fb-label">Nama Properti</label><input type="text" name="name" class="fb-input" placeholder="cth: Website Utama" required></div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="fb-field"><label class="fb-label">Property ID</label><input type="text" name="property_id" class="fb-input" placeholder="123456789"></div>
                    <div class="fb-field"><label class="fb-label">Website</label><input type="url" name="website" class="fb-input" placeholder="https://..."></div>
                </div>
                <button class="fb-btn fb-btn-primary w-full">Simpan Properti</button>
            </form>
        </div>
    </div>
    </template>

    <!-- Modal: Input manual -->
    <template x-teleport="body">
    <div x-show="addOpen" x-cloak class="fb-modal-center" @click.self="addOpen = false">
        <div class="fb-modal-card">
            <div class="fb-card-title mb-4">Input Insight Manual</div>
            <form method="POST" action="{{ route('tools.analytics.store') }}">
                @csrf
                <div class="fb-field">
                    <label class="fb-label">Properti</label>
                    <select name="property_id" class="fb-select" required>
                        @foreach($properties as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div class="fb-field"><label class="fb-label">Tanggal</label><input type="date" name="date" class="fb-input" value="{{ date('Y-m-d') }}" required></div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="fb-field"><label class="fb-label">Users</label><input type="number" name="users" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">New Users</label><input type="number" name="new_users" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Sessions</label><input type="number" name="sessions" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Pageviews</label><input type="number" name="pageviews" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Avg. Duration (detik)</label><input type="number" name="avg_session_duration" class="fb-input" min="0" value="0"></div>
                    <div class="fb-field"><label class="fb-label">Bounce Rate (%)</label><input type="number" name="bounce_rate" class="fb-input" min="0" step="0.1" value="0"></div>
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
            <p class="fb-card-sub mb-4">Kolom: date, users, new_users, sessions, pageviews, avg_session_duration, bounce_rate</p>
            <form method="POST" action="{{ route('tools.analytics.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="fb-field">
                    <label class="fb-label">Properti</label>
                    <select name="property_id" class="fb-select" required>
                        @foreach($properties as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
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
            id: 'ga-trend',
            config: {
                type: 'line',
                labels: @json($usersSeries['labels']),
                datasets: [
                    { label: 'Users', data: @json($usersSeries['values']), color: '#0A84FF', fill: true },
                    { label: 'Sessions', data: @json($sessionsSeries['values']), color: '#AF52DE', fill: false },
                    { label: 'Pageviews', data: @json($pageviewsSeries['values']), color: '#FF9500', fill: false },
                ],
                legend: true,
            },
        },
        {
            id: 'ga-channels',
            config: { type: 'doughnut', labels: {{ Js::from(array_column($channels, 'name')) }}, datasets: [{ data: {{ Js::from(array_column($channels, 'users')) }} }], legend: true },
        },
        {
            id: 'ga-new',
            config: {
                type: 'bar',
                labels: @json($newUsersSeries['labels']),
                datasets: [
                    { label: 'New Users', data: @json($newUsersSeries['values']), color: '#34C759' },
                    { label: 'Sessions', data: @json($sessionsSeries['values']), color: '#5AC8FA' },
                ],
                legend: true,
            },
        },
    ];
</script>
@endsection