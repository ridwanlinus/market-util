@extends('layouts.app')

@section('title', 'Perusahaan')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 fb-fade-up">
        <div>
            <h1 class="fb-title">Perusahaan 🏢</h1>
            <p class="fb-subtitle">Semua perusahaan terdaftar di Freebuff Marketing Suite.</p>
        </div>
        <span class="fb-chip">{{ $companies->count() }} perusahaan</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($summary as $c)
        <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:{{ $loop->index * 0.04 }}s">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-ios-blue to-ios-purple text-white flex items-center justify-center font-extrabold text-lg shrink-0">
                    {{ strtoupper(substr($c['name'], 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="font-bold text-[15px] truncate">{{ $c['name'] }}</div>
                    <div class="text-[12px] text-ios-gray truncate">{{ $c['industry'] ?: '—' }}@if($c['website']) · {{ $c['website'] }}@endif</div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="bg-ios-gray rounded-xl py-2.5">
                    <div class="font-extrabold fb-num">{{ $c['users'] }}</div>
                    <div class="text-[10.5px] text-ios-gray font-semibold">USERS</div>
                </div>
                <div class="bg-ios-gray rounded-xl py-2.5">
                    <div class="font-extrabold fb-num">{{ $c['contents'] }}</div>
                    <div class="text-[10.5px] text-ios-gray font-semibold">KONTEN</div>
                </div>
                <div class="bg-ios-gray rounded-xl py-2.5">
                    <div class="font-extrabold fb-num">{{ $c['pending'] }}</div>
                    <div class="text-[10.5px] text-ios-gray font-semibold">PENDING</div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-black/5 flex items-center justify-between text-[12px] text-ios-gray">
                <span>Meta: <b>{{ $c['meta_pages'] }}</b> halaman</span>
                <span>Ads: <b>{{ $c['ads_campaigns'] }}</b> campaign</span>
                <span>GA: <b>{{ $c['ga_properties'] }}</b> properti</span>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <span class="text-[12px] text-ios-gray"><b class="text-green-600">{{ $c['approved'] }}</b> disetujui</span>
                @if($c['pending'] > 0)
                <a href="{{ route('super.index') }}" class="fb-btn fb-btn-ghost fb-btn-xs">Tinjau antrian <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full fb-card fb-empty">
            <div class="icon">🏢</div>
            <p>Belum ada perusahaan terdaftar.</p>
        </div>
        @endforelse
    </div>
@endsection