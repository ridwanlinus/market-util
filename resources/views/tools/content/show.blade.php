@extends('layouts.app')

@section('title', $content->title)

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 fb-fade-up">
        <div>
            <a href="{{ route('tools.content.index') }}" class="text-[13px] font-semibold text-ios-blue mb-1 inline-flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Studio
            </a>
            <h1 class="fb-title mt-1">{{ $content->title }}</h1>
            <p class="fb-subtitle">
                <span class="fb-badge {{ match($content->status) { 'approved' => 'fb-badge-green', 'pending' => 'fb-badge-orange', 'rejected' => 'fb-badge-red', default => 'fb-badge-gray' } }}">{{ $content->statusLabel() }}</span>
                <span class="ml-2">{{ $content->type === 'carousel' ? 'Carousel · ' . $content->slides_count . ' slide' : 'Single image' }} · 4:5 (1080×1350)</span>
            </p>
        </div>
        <div class="flex gap-2">
            @if(in_array($content->status, ['draft', 'rejected']))
            <a href="{{ route('tools.content.create') }}?edit={{ $content->id }}" class="fb-btn fb-btn-ghost fb-btn-sm">
                <i data-lucide="pencil" class="w-4 h-4"></i> Edit
            </a>
            <form method="POST" action="{{ route('tools.content.submit', $content) }}">
                @csrf
                <button type="submit" class="fb-btn fb-btn-primary fb-btn-sm">
                    <i data-lucide="send" class="w-4 h-4"></i> Kirim Approval
                </button>
            </form>
            @endif
            @if($content->status === 'pending')
            <span class="fb-btn fb-btn-ghost fb-btn-sm opacity-60 pointer-events-none"><i data-lucide="clock" class="w-4 h-4"></i> Menunggu Super Admin</span>
            @endif
            <form method="POST" action="{{ route('tools.content.destroy', $content) }}" onsubmit="return confirm('Hapus konten ini?');">
                @csrf @method('DELETE')
                <button type="submit" class="fb-btn fb-btn-danger fb-btn-sm"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Preview -->
        <div class="lg:col-span-2 fb-fade-up" style="animation-delay:0.05s">
            @if($content->type === 'carousel')
            <div x-data="{ slide: 0 }" class="fb-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="fb-card-title">Preview Carousel</div>
                    <div class="flex items-center gap-1">
                        @foreach($content->fileUrls() as $i => $url)
                        <button @click="slide = {{ $i }}" class="w-7 h-7 rounded-lg text-[11px] font-bold transition"
                                :class="slide === {{ $i }} ? 'bg-ios-blue text-white' : 'bg-ios-gray text-ios-gray'">{{ $i + 1 }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-center bg-ios-gray rounded-2xl py-8">
                    <template x-for="(url, i) in @json($content->fileUrls())" :key="i">
                        <div x-show="slide === i" class="w-[320px] max-w-[75vw] rounded-xl overflow-hidden shadow-2xl" x-transition>
                            <img :src="url" class="w-full aspect-[4/5] object-cover" alt="">
                        </div>
                    </template>
                </div>
                <div class="flex justify-center gap-1.5 mt-4">
                    <template x-for="(url, i) in @json($content->fileUrls())" :key="'dot'+i">
                        <span class="w-2 h-2 rounded-full transition" :class="slide === i ? 'bg-ios-blue' : 'bg-gray-300'"></span>
                    </template>
                </div>
            </div>
            @else
            <div class="fb-card p-5">
                <div class="fb-card-title mb-4">Preview</div>
                <div class="flex justify-center bg-ios-gray rounded-2xl py-8">
                    @if($content->coverUrl())
                    <img src="{{ $content->coverUrl() }}" class="w-[340px] max-w-[75vw] rounded-xl shadow-2xl aspect-[4/5] object-cover" alt="">
                    @endif
                </div>
            </div>
            @endif

            @if($content->caption)
            <div class="fb-card fb-card-pad mt-4">
                <div class="fb-card-title mb-2">Caption</div>
                <p class="text-[14.5px] leading-relaxed text-gray-700 whitespace-pre-line">{{ $content->caption }}</p>
            </div>
            @endif
        </div>

        <!-- Info & approval -->
        <div class="space-y-4 fb-fade-up" style="animation-delay:0.1s">
            <div class="fb-card fb-card-pad">
                <div class="fb-card-title mb-3">Detail Konten</div>
                <dl class="space-y-3 text-[13.5px]">
                    <div class="flex justify-between"><dt class="text-ios-gray">Dibuat oleh</dt><dd class="font-semibold">{{ optional($content->user)->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ios-gray">Dibuat</dt><dd class="font-semibold">{{ $content->created_at->translatedFormat('d M Y, H:i') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ios-gray">Jenis</dt><dd class="font-semibold capitalize">{{ $content->type }} · {{ $content->slides_count }} gambar</dd></div>
                    <div class="flex justify-between"><dt class="text-ios-gray">Platform</dt><dd class="font-semibold">{{ $content->platform ?: '—' }}</dd></div>
                    @if($content->scheduled_at)
                    <div class="flex justify-between"><dt class="text-ios-gray">Jadwal</dt><dd class="font-semibold">{{ $content->scheduled_at->translatedFormat('d M Y H:i') }}</dd></div>
                    @endif
                </dl>
                <a href="{{ route('tools.content.create') }}?edit={{ $content->id }}" class="fb-btn fb-btn-ghost w-full mt-4">Edit di Studio</a>
            </div>

            <div class="fb-card fb-card-pad">
                <div class="fb-card-title mb-3 flex items-center gap-2">
                    Approval
                    <span class="fb-badge {{ match($content->status) { 'approved' => 'fb-badge-green', 'pending' => 'fb-badge-orange', 'rejected' => 'fb-badge-red', default => 'fb-badge-gray' } }}">{{ $content->statusLabel() }}</span>
                </div>
                @if($content->status === 'approved')
                <p class="text-[13.5px] text-gray-600 leading-relaxed">
                    ✅ Disetujui oleh <b>{{ optional($content->approver)->name }}</b> pada {{ $content->approved_at?->translatedFormat('d M Y, H:i') }}.
                </p>
                @elseif($content->status === 'rejected')
                <p class="text-[13.5px] text-red-600 leading-relaxed">
                    ✖️ Ditolak. <b>Catatan:</b> {{ $content->approval_note ?: 'tanpa catatan' }}
                </p>
                <form method="POST" action="{{ route('tools.content.submit', $content) }}" class="mt-3">
                    @csrf
                    <button class="fb-btn fb-btn-primary w-full fb-btn-sm">Perbaiki & Kirim Ulang</button>
                </form>
                @elseif($content->status === 'pending')
                <p class="text-[13.5px] text-gray-600 leading-relaxed">
                    ⏳ Konten sedang menunggu persetujuan Super Admin. Anda akan melihat hasilnya di sini.
                </p>
                @else
                <p class="text-[13.5px] text-gray-600 leading-relaxed">
                    Konten masih draf. Kirim untuk approval agar bisa dipublikasikan.
                </p>
                <form method="POST" action="{{ route('tools.content.submit', $content) }}" class="mt-3">
                    @csrf
                    <button class="fb-btn fb-btn-primary w-full fb-btn-sm">Kirim Approval</button>
                </form>
                @endif
            </div>

            <div class="fb-card fb-card-pad !py-4 flex items-center justify-between">
                <div class="text-[13px] text-ios-gray">Unduh semua gambar</div>
                @foreach($content->fileUrls() as $i => $url)
                <a href="{{ $url }}" download="{{ $content->title }}-{{ $i + 1 }}.png" class="fb-btn fb-btn-ghost fb-btn-xs">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i> Slide {{ $i + 1 }}
                </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection