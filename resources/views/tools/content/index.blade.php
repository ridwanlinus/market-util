@extends('layouts.app')

@section('title', 'Content Studio')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 fb-fade-up">
        <div>
            <h1 class="fb-title">Content Studio 🎨</h1>
            <p class="fb-subtitle">Produksi konten single image & carousel 4:5 — siap untuk persetujuan Super Admin.</p>
        </div>
        <a href="{{ route('tools.content.create') }}" class="fb-btn fb-btn-primary fb-btn-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Buat Konten
        </a>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6 fb-fade-up" style="animation-delay:0.05s">
        <div class="fb-card fb-card-pad !py-4">
            <div class="fb-kpi">
                <div class="fb-kpi-icon bg-blue-50 text-ios-blue"><i data-lucide="images" class="w-5 h-5"></i></div>
                <div><div class="fb-kpi-value">{{ $stats->total ?? 0 }}</div><div class="fb-kpi-label">Total Konten</div></div>
            </div>
        </div>
        <div class="fb-card fb-card-pad !py-4">
            <div class="fb-kpi">
                <div class="fb-kpi-icon bg-green-50 text-green-600"><i data-lucide="check-circle-2" class="w-5 h-5"></i></div>
                <div><div class="fb-kpi-value">{{ $stats->approved ?? 0 }}</div><div class="fb-kpi-label">Disetujui</div></div>
            </div>
        </div>
        <div class="fb-card fb-card-pad !py-4">
            <div class="fb-kpi">
                <div class="fb-kpi-icon bg-orange-50 text-ios-orange"><i data-lucide="clock" class="w-5 h-5"></i></div>
                <div><div class="fb-kpi-value">{{ $stats->pending ?? 0 }}</div><div class="fb-kpi-label">Menunggu Approval</div></div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" class="flex flex-wrap items-center gap-2 mb-5 fb-fade-up" style="animation-delay:0.1s">
        <a href="{{ route('tools.content.index') }}" class="fb-chip {{ !request('status') && !request('type') ? 'active' : '' }}">Semua</a>
        <a href="{{ route('tools.content.index', ['type' => 'single']) }}" class="fb-chip {{ request('type') === 'single' ? 'active' : '' }}">Single Image</a>
        <a href="{{ route('tools.content.index', ['type' => 'carousel']) }}" class="fb-chip {{ request('type') === 'carousel' ? 'active' : '' }}">Carousel</a>
        <div class="w-px h-6 bg-black/10 mx-1"></div>
        @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'draft' => 'Draft', 'rejected' => 'Ditolak'] as $key => $label)
        <a href="{{ route('tools.content.index', ['status' => $key]) }}" class="fb-chip {{ request('status') === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </form>

    <!-- Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($contents as $c)
        <a href="{{ route('tools.content.show', $c) }}" class="fb-card overflow-hidden group block fb-fade-up" style="animation-delay:{{ $loop->index * 0.02 }}s">
            <div class="relative overflow-hidden bg-ios-gray">
                @if($c->coverUrl())
                <img src="{{ $c->coverUrl() }}" class="w-full aspect-[4/5] object-cover group-hover:scale-105 transition duration-300" alt="">
                @else
                <div class="w-full aspect-[4/5] flex items-center justify-center bg-gradient-to-br from-ios-blue to-ios-purple text-white text-5xl font-bold">{{ strtoupper(substr($c->title,0,1)) }}</div>
                @endif
                <div class="absolute top-2 left-2 flex gap-1.5">
                    <span class="fb-badge {{ match($c->status) { 'approved' => 'fb-badge-green', 'pending' => 'fb-badge-orange', 'rejected' => 'fb-badge-red', default => 'fb-badge-gray' } }}">{{ $c->statusLabel() }}</span>
                </div>
                <div class="absolute top-2 right-2">
                    <span class="fb-badge !bg-black/50 !text-white backdrop-blur">
                        <i data-lucide="{{ $c->type === 'carousel' ? 'images' : 'image' }}" class="w-3 h-3"></i> {{ $c->type === 'carousel' ? 'Carousel ' . $c->slides_count : 'Single' }}
                    </span>
                </div>
            </div>
            <div class="p-3.5">
                <div class="font-semibold text-[14.5px] truncate">{{ $c->title }}</div>
                <div class="text-[12px] text-ios-gray mt-0.5">{{ $c->created_at->diffForHumans() }} · {{ optional($c->user)->name }}</div>
            </div>
        </a>
        @empty
        <div class="col-span-full fb-card fb-empty">
            <div class="icon">🎨</div>
            <div class="font-semibold text-ios-label mb-1">Belum ada konten</div>
            <p>Buat konten pertama Anda — single image atau carousel 4:5.</p>
            <a href="{{ route('tools.content.create') }}" class="fb-btn fb-btn-primary fb-btn-sm mt-4">Buat Konten</a>
        </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $contents->links() }}</div>
@endsection