@extends('layouts.app')

@section('title', 'Engagement Rate')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 fb-fade-up">
        <div>
            <h1 class="fb-title">Engagement Rate Calculator 💞</h1>
            <p class="fb-subtitle">Hitung engagement rate konten Meta: (total interaksi ÷ followers) × 100.</p>
        </div>
        <span class="fb-chip"><i data-lucide="zap" class="w-3.5 h-3.5 text-ios-orange"></i> Real-time kalkulasi</span>
    </div>

    <div x-data="erCalculator()" class="grid grid-cols-1 lg:grid-cols-3 gap-5" x-init="init()">
        <!-- ===== Kalkulator ===== -->
        <div class="fb-card fb-card-pad lg:col-span-2 fb-fade-up" style="animation-delay:0.05s">
            <div class="fb-card-title mb-4">Kalkulator Cepat</div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                <div class="fb-field !mb-0">
                    <label class="fb-label">❤️ Likes</label>
                    <input type="number" min="0" class="fb-input" x-model.number="likes" @input="calc()" placeholder="0">
                </div>
                <div class="fb-field !mb-0">
                    <label class="fb-label">💬 Comments</label>
                    <input type="number" min="0" class="fb-input" x-model.number="comments" @input="calc()" placeholder="0">
                </div>
                <div class="fb-field !mb-0">
                    <label class="fb-label">📤 Shares</label>
                    <input type="number" min="0" class="fb-input" x-model.number="shares" @input="calc()" placeholder="0">
                </div>
                <div class="fb-field !mb-0">
                    <label class="fb-label">🔖 Saves</label>
                    <input type="number" min="0" class="fb-input" x-model.number="saves" @input="calc()" placeholder="0">
                </div>
            </div>

            <div class="fb-field">
                <label class="fb-label">👥 Jumlah Followers</label>
                <input type="number" min="1" class="fb-input" x-model.number="followers" @input="calc()" placeholder="cth: 12000">
            </div>

            <!-- Hasil live -->
            <div class="mt-5 p-5 rounded-2xl" :style="'background:linear-gradient(135deg,' + result.gradeColor + '22,' + result.gradeColor + '0a)'">
                <div class="flex flex-wrap items-center gap-6">
                    <div class="fb-ring" :style="'--p:' + ringPercent + ';--c:' + result.gradeColor + ';--s:130px'">
                        <div class="text-center">
                            <div class="text-[26px] font-extrabold fb-num" x-text="result.rate.toFixed(2) + '%'"></div>
                            <div class="text-[11px] text-ios-gray font-semibold">ER</div>
                        </div>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <div class="text-[17px] font-bold" x-text="result.grade"></div>
                        <p class="text-[13px] text-gray-500 mt-0.5" x-text="result.label"></p>
                        <div class="flex gap-4 mt-3 text-[13px]">
                            <span class="text-ios-gray">Interaksi: <b class="text-ios-label fb-num" x-text="fb(totalInteractions)"></b></span>
                            <span class="text-ios-gray">Followers: <b class="text-ios-label fb-num" x-text="fb(followers)"></b></span>
                        </div>
                        <div class="flex gap-2 mt-1 text-[12px] text-ios-gray">
                            <span>❤️ <b class="fb-num" x-text="fb(likes)"></b></span>
                            <span>💬 <b class="fb-num" x-text="fb(comments)"></b></span>
                            <span>📤 <b class="fb-num" x-text="fb(shares)"></b></span>
                            <span>🔖 <b class="fb-num" x-text="fb(saves)"></b></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <label class="fb-label">Nama Perhitungan</label>
                <div class="flex gap-2">
                    <input type="text" id="calc-name" class="fb-input" placeholder="cth: Post Promo Oktober">
                    <button class="fb-btn fb-btn-primary shrink-0" @click="save()">Simpan Hasil</button>
                </div>
            </div>

            <form id="calc-form" method="POST" action="{{ route('tools.engagement.calculate') }}" class="hidden">
                @csrf
                <input type="hidden" name="name" id="hf-name">
                <input type="hidden" name="likes" id="hf-likes">
                <input type="hidden" name="comments" id="hf-comments">
                <input type="hidden" name="shares" id="hf-shares">
                <input type="hidden" name="saves" id="hf-saves">
                <input type="hidden" name="followers" id="hf-followers">
            </form>
        </div>

        <!-- ===== Dari Post Meta ===== -->
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.1s">
            <div class="fb-card-title mb-1">Dari Konten Meta</div>
            <div class="fb-card-sub mb-4">Pilih post yang sudah disinkronkan</div>
            @if($posts->count())
            <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                @foreach($posts as $p)
                <button data-post-id="{{ $p->id }}"
                        data-likes="{{ $p->likes }}" data-comments="{{ $p->comments }}"
                        data-shares="{{ $p->shares }}" data-saves="{{ $p->saves }}"
                        data-followers="{{ $p->followers_count }}"
                        class="post-pick w-full text-left rounded-xl border border-black/5 hover:border-ios-blue/50 hover:bg-blue-50/30 transition p-3">
                    <div class="text-[13px] font-semibold truncate">{{ $p->message ? \Illuminate\Support\Str::limit($p->message, 60) : 'Konten ' . optional($p->posted_at)->format('d M') }}</div>
                    <div class="text-[11.5px] text-ios-gray mt-1 flex gap-2">
                        <span>{{ optional($p->posted_at)->translatedFormat('d M Y') }}</span>
                        <span>❤️ {{ number_format($p->likes) }}</span>
                        <span>💬 {{ number_format($p->comments) }}</span>
                        <span>📤 {{ number_format($p->shares) }}</span>
                    </div>
                    @if(($p->followers_count ?? 0) > 0)
                    <div class="text-[12px] font-bold mt-1" style="color:{{ ($p->totalInteractions() / $p->followers_count * 100) >= 3 ? '#34C759' : '#FF9500' }}">
                        ER {{ number_format(($p->totalInteractions() / $p->followers_count) * 100, 2, ',', '.') }}%
                    </div>
                    @endif
                </button>
                @endforeach
            </div>
            @else
            <div class="fb-empty !py-10">
                <div class="icon">📭</div>
                <p class="text-[13px]">Belum ada post Meta.<br>Input post di <b>Meta Insights</b> dulu.</p>
                <a href="{{ route('tools.meta.index') }}" class="fb-btn fb-btn-ghost fb-btn-sm mt-3">Buka Meta Insights</a>
            </div>
            @endif
        </div>
    </div>

    <!-- ===== Grafik ===== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-5">
        <div class="fb-card fb-card-pad lg:col-span-2 fb-fade-up" style="animation-delay:0.15s">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="fb-card-title">Tren Engagement Rate per Konten</div>
                    <div class="fb-card-sub">Rata-rata: <b>{{ $avgEr }}%</b> · Benchmark industri 1.5–3%</div>
                </div>
            </div>
            <div class="h-[280px]">
                <canvas id="er-chart"></canvas>
            </div>
        </div>

        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.2s">
            <div class="fb-card-title mb-1">Komposisi Interaksi</div>
            <div class="fb-card-sub mb-4">Total dari semua post Meta</div>
            <div class="h-[240px]">
                <canvas id="er-donut"></canvas>
            </div>
            <div class="mt-3 space-y-2 text-[13px]">
                @foreach([['❤️ Likes', $interactionTotals['likes'], '#FF2D55'], ['💬 Comments', $interactionTotals['comments'], '#0A84FF'], ['📤 Shares', $interactionTotals['shares'], '#34C759'], ['🔖 Saves', $interactionTotals['saves'], '#AF52DE']] as [$label, $val, $color])
                <div class="flex items-center gap-2">
                    <span class="fb-dot" style="background:{{ $color }}"></span>
                    <span class="flex-1 text-ios-gray">{{ $label }}</span>
                    <b class="fb-num">{{ number_format($val) }}</b>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ===== Riwayat ===== -->
    <div class="fb-card fb-card-pad mt-5 fb-fade-up" style="animation-delay:0.25s">
        <div class="fb-card-title mb-4">Riwayat Perhitungan</div>
        @if($saved->count())
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($saved as $c)
            <div class="rounded-2xl border border-black/5 p-4 flex items-center gap-3 hover:border-ios-blue/40 transition">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-extrabold text-[15px] shrink-0"
                     style="background:{{ $c->result >= 3 ? 'rgba(52,199,89,0.14)' : ($c->result >= 1.5 ? 'rgba(10,132,255,0.12)' : 'rgba(255,149,0,0.14)') }};color:{{ $c->result >= 3 ? '#1E9E47' : ($c->result >= 1.5 ? '#0A84FF' : '#D67A00') }}">
                    {{ number_format($c->result, 2, ',', '.') }}%
                </div>
                <div class="min-w-0 flex-1">
                    <div class="font-semibold text-[13.5px] truncate">{{ $c->name }}</div>
                    <div class="text-[12px] text-ios-gray">{{ $c->created_at->translatedFormat('d M Y, H:i') }}</div>
                    @if($c->inputs)
                    <div class="text-[11px] text-ios-gray mt-0.5 fb-num">❤️ {{ number_format($c->inputs['likes'] ?? 0) }} · 💬 {{ number_format($c->inputs['comments'] ?? 0) }} · 👥 {{ number_format($c->inputs['followers'] ?? 0) }}</div>
                    @endif
                </div>
                <form method="POST" action="{{ route('tools.engagement.destroy', $c) }}" onsubmit="return confirm('Hapus?');">
                    @csrf @method('DELETE')
                    <button class="fb-editor-tool text-ios-gray"><i data-lucide="x" class="w-4 h-4"></i></button>
                </form>
            </div>
            @endforeach
        </div>
        @else
        <div class="fb-empty !py-8"><div class="icon">🧮</div><p>Belum ada perhitungan tersimpan.</p></div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    window.PAGE_CHARTS = [
        {
            id: 'er-chart',
            config: {
                type: 'bar',
                labels: @json($erSeries->pluck('label')),
                datasets: [{ label: 'ER %', data: @json($erSeries->pluck('value')), color: '#0A84FF' }],
                legend: false,
                tooltip: 'percent',
            },
        },
        {
            id: 'er-donut',
            config: {
                type: 'doughnut',
                labels: ['Likes', 'Comments', 'Shares', 'Saves'],
                datasets: [{ data: {{ Js::from([$interactionTotals['likes'], $interactionTotals['comments'], $interactionTotals['shares'], $interactionTotals['saves']]) }} }],
                legend: true,
            },
        },
    ];

    function erCalculator() {
        return {
            likes: 0, comments: 0, shares: 0, saves: 0, followers: 12000,
            result: { rate: 0, grade: '—', label: 'Isi data untuk mulai menghitung.', gradeColor: '#8E8E93' },
            get totalInteractions() { return this.likes + this.comments + this.shares + this.saves; },
            get ringPercent() { return Math.min(this.result.rate * 20, 100); },
            fb: function (n) { return (Number(n) || 0).toLocaleString('id-ID'); },
            init: function () {
                var self = this;
                document.querySelectorAll('.post-pick').forEach(function (el) {
                    el.addEventListener('click', function () {
                        self.likes = +el.dataset.likes || 0;
                        self.comments = +el.dataset.comments || 0;
                        self.shares = +el.dataset.shares || 0;
                        self.saves = +el.dataset.saves || 0;
                        self.followers = +el.dataset.followers || 0;
                        self.calc();
                        document.getElementById('calc-name').value = 'ER ' + el.querySelector('.text-[13px]').textContent.trim().slice(0, 40);
                        document.getElementById('calc-name').focus();
                    });
                });
            },
            calc: function () {
                var t = this.totalInteractions;
                if (!this.followers || this.followers <= 0) { this.result = { rate: 0, grade: '—', label: 'Masukkan jumlah followers.', gradeColor: '#8E8E93' }; return; }
                var rate = (t / this.followers) * 100;
                var grade, label, color;
                if (rate >= 5) { grade = 'Excellent'; label = 'Konten sangat engaging — pertahankan!'; color = '#34C759'; }
                else if (rate >= 3) { grade = 'Good'; label = 'Performa di atas rata-rata industri.'; color = '#34C759'; }
                else if (rate >= 1.5) { grade = 'Average'; label = 'Sesuai rata-rata industri (1.5–3%).'; color = '#0A84FF'; }
                else if (rate >= 0.5) { grade = 'Below Average'; label = 'Di bawah rata-rata, coba optimalkan konten.'; color = '#FF9500'; }
                else { grade = 'Poor'; label = 'Performa rendah — perlu perbaikan strategi.'; color = '#FF3B30'; }
                this.result = { rate: rate, grade: grade, label: label, gradeColor: color };
            },
            save: function () {
                var name = document.getElementById('calc-name').value.trim();
                if (!name) { FB.toast('Isi nama perhitungan dulu.', 'error'); return; }
                if (!this.followers || this.followers <= 0) { FB.toast('Followers wajib diisi.', 'error'); return; }
                document.getElementById('hf-name').value = name;
                document.getElementById('hf-likes').value = this.likes;
                document.getElementById('hf-comments').value = this.comments;
                document.getElementById('hf-shares').value = this.shares;
                document.getElementById('hf-saves').value = this.saves;
                document.getElementById('hf-followers').value = this.followers;
                document.getElementById('calc-form').submit();
            },
        };
    }
</script>
@endsection