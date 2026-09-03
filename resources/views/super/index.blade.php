@extends('layouts.app')

@section('title', 'Super Admin')

@section('content')
<div x-data="{ noteId: null, note: '', action: 'approve' }">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 fb-fade-up">
        <div>
            <h1 class="fb-title">Super Admin Console 🛡️</h1>
            <p class="fb-subtitle">Tinjau & setujui konten dari semua perusahaan.</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">
        @php
            $cards = [
                ['Perusahaan', $stats['companies'], 'building-2', 'bg-blue-50 text-ios-blue'],
                ['Total Pengguna', $stats['users'], 'users', 'bg-teal-50 text-teal-600'],
                ['Total Konten', $stats['contents'], 'images', 'bg-purple-50 text-ios-purple'],
                ['Pending', $stats['pending'], 'clock', 'bg-orange-50 text-ios-orange'],
                ['Disetujui', $stats['approved'], 'check-circle-2', 'bg-green-50 text-green-600'],
                ['Ditolak', $stats['rejected'], 'x-circle', 'bg-red-50 text-ios-red'],
            ];
        @endphp
        @foreach($cards as $i => [$label, $value, $icon, $bg])
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:{{ $i * 0.04 }}s">
            <div class="fb-kpi-icon {{ $bg }} mb-2"><i data-lucide="{{ $icon }}" class="w-5 h-5"></i></div>
            <div class="fb-kpi-value fb-num">{{ $value }}</div>
            <div class="fb-kpi-label">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    <!-- Approval queue -->
    <div class="fb-card fb-card-pad mb-6 fb-fade-up" style="animation-delay:0.1s">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="fb-card-title">Antrian Approval</div>
                <div class="fb-card-sub">Konten menunggu persetujuan Anda</div>
            </div>
            @if($pending->total() > 0)<span class="fb-badge fb-badge-orange">{{ $pending->total() }} antrian</span>@endif
        </div>

        @forelse($pending as $c)
        <div class="flex flex-col sm:flex-row gap-4 py-4 {{ !$loop->first ? 'border-t border-black/5' : '' }}">
            <div class="w-24 h-[120px] rounded-xl overflow-hidden bg-ios-gray shrink-0">
                @if($c->coverUrl())
                <img src="{{ $c->coverUrl() }}" class="w-full h-full object-cover" alt="">
                @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-ios-blue to-ios-purple text-white text-2xl font-bold">{{ strtoupper(substr($c->title, 0, 1)) }}</div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-bold text-[15.5px]">{{ $c->title }}</h3>
                    <span class="fb-badge {{ $c->type === 'carousel' ? 'fb-badge-blue' : 'fb-badge-gray' }}">{{ $c->type === 'carousel' ? 'Carousel ' . $c->slides_count : 'Single' }}</span>
                </div>
                <div class="text-[12.5px] text-ios-gray mt-1">
                    <b>{{ optional($c->company)->name }}</b> · oleh {{ optional($c->user)->name }} · {{ $c->created_at->diffForHumans() }}
                </div>
                @if($c->caption)
                <p class="text-[13px] text-gray-600 mt-1.5 line-clamp-2">{{ $c->caption }}</p>
                @endif
                <div class="flex flex-wrap gap-2 mt-3">
                    <a href="{{ route('tools.content.show', $c) }}" target="_blank" class="fb-btn fb-btn-ghost fb-btn-xs"><i data-lucide="eye" class="w-3.5 h-3.5"></i> Preview</a>
                    <button @click="noteId = {{ $c->id }}; action = 'approve'; note = ''" class="fb-btn fb-btn-green fb-btn-xs"><i data-lucide="check" class="w-3.5 h-3.5"></i> Setujui</button>
                    <button @click="noteId = {{ $c->id }}; action = 'reject'; note = ''" class="fb-btn fb-btn-danger fb-btn-xs"><i data-lucide="x" class="w-3.5 h-3.5"></i> Tolak</button>
                </div>
            </div>
        </div>
        @empty
        <div class="fb-empty !py-14">
            <div class="icon">🎉</div>
            <div class="font-semibold text-ios-label mb-1">Tidak ada antrian</div>
            <p>Semua konten sudah ditinjau.</p>
        </div>
        @endforelse

        @if($pending->hasPages())
        <div class="mt-4">{{ $pending->links() }}</div>
        @endif
    </div>

    <!-- Recent -->
    <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.14s">
        <div class="fb-card-title mb-4">Aktivitas Terakhir</div>
        @if($recent->count())
        <div class="fb-table-wrap !shadow-none !border-0">
            <div class="overflow-x-auto">
                <table class="fb-table">
                    <thead><tr><th>Konten</th><th>Perusahaan</th><th>Status</th><th>Dibuat</th></tr></thead>
                    <tbody>
                    @foreach($recent as $c)
                    <tr>
                        <td class="font-semibold">{{ \Illuminate\Support\Str::limit($c->title, 40) }}</td>
                        <td>{{ optional($c->company)->name }}</td>
                        <td>
                            <span class="fb-badge {{ match($c->status) { 'approved' => 'fb-badge-green', 'pending' => 'fb-badge-orange', 'rejected' => 'fb-badge-red', default => 'fb-badge-gray' } }}">{{ $c->statusLabel() }}</span>
                        </td>
                        <td class="text-ios-gray">{{ $c->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Modal review -->
    <template x-teleport="body">
    <div x-show="noteId !== null" x-cloak class="fb-modal-center" @click.self="noteId = null">
        <div class="fb-modal-card">
            <div class="fb-card-title mb-1" x-text="action === 'approve' ? 'Setujui Konten ✅' : 'Tolak Konten ✖️'"></div>
            <p class="fb-card-sub mb-4">Tambahkan catatan (wajib jika menolak).</p>
            <textarea x-model="note" class="fb-textarea" rows="3" placeholder="Catatan untuk tim kreatif..."></textarea>
            <div class="flex gap-2 mt-4">
                <button @click="noteId = null" class="fb-btn fb-btn-ghost flex-1">Batal</button>
                <template x-if="action === 'approve'">
                    <form method="POST" :action="'/super/contents/' + noteId + '/approve'" class="flex-1">
                        @csrf
                        <input type="hidden" name="note" :value="note">
                        <button class="fb-btn fb-btn-green w-full">Setujui</button>
                    </form>
                </template>
                <template x-if="action === 'reject'">
                    <form method="POST" :action="'/super/contents/' + noteId + '/reject'" class="flex-1">
                        @csrf
                        <input type="hidden" name="note" :value="note">
                        <button class="fb-btn fb-btn-danger w-full" :disabled="!note.trim()">Tolak</button>
                    </form>
                </template>
            </div>
        </div>
    </div>
    </template>
</div>
@endsection