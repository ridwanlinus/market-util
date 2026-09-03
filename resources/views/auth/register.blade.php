@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
    <h2 class="text-[22px] font-extrabold tracking-tight mb-1">Buat akun perusahaan 🚀</h2>
    <p class="text-ios-gray text-[14px] mb-6">Mulai produksi konten dan pantau insight marketing Anda.</p>

    @if($errors->any())
        <div class="bg-red-50 border border-red-100 rounded-xl p-3 mb-4 text-red-600 text-[13px] font-medium">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div class="fb-field">
            <label class="fb-label">Nama Perusahaan</label>
            <input type="text" name="company_name" value="{{ old('company_name') }}" required
                   class="fb-input" placeholder="PT. Kreatif Nusantara">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="fb-field">
                <label class="fb-label">Nama Anda</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="fb-input" placeholder="Nama lengkap">
            </div>
            <div class="fb-field">
                <label class="fb-label">Industri</label>
                <input type="text" name="industry" value="{{ old('industry') }}" class="fb-input" placeholder="F&B, Fashion...">
            </div>
        </div>
        <div class="fb-field">
            <label class="fb-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="fb-input" placeholder="nama@perusahaan.com">
        </div>
        <div class="fb-field">
            <label class="fb-label">Website</label>
            <input type="url" name="website" value="{{ old('website') }}" class="fb-input" placeholder="https://...">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="fb-field">
                <label class="fb-label">Password</label>
                <input type="password" name="password" required class="fb-input" placeholder="Min. 8 karakter">
            </div>
            <div class="fb-field">
                <label class="fb-label">Konfirmasi</label>
                <input type="password" name="password_confirmation" required class="fb-input" placeholder="Ulangi password">
            </div>
        </div>
        <button type="submit" class="fb-btn fb-btn-primary w-full !py-4">Buat Akun</button>
    </form>

    <div class="mt-6 pt-5 border-t border-black/5 text-center text-[13.5px] text-ios-gray">
        Sudah punya akun? <a href="{{ route('login') }}" class="text-ios-blue font-semibold">Masuk</a>
    </div>
@endsection