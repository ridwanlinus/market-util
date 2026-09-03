@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <h2 class="text-[22px] font-extrabold tracking-tight mb-1">Selamat datang kembali 👋</h2>
    <p class="text-ios-gray text-[14px] mb-6">Masuk untuk mengakses dashboard marketing Anda.</p>

    @if($errors->any())
        <div class="bg-red-50 border border-red-100 rounded-xl p-3 mb-4 text-red-600 text-[13px] font-medium">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div class="fb-field">
            <label class="fb-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="fb-input" placeholder="nama@perusahaan.com">
        </div>
        <div class="fb-field">
            <label class="fb-label">Password</label>
            <input type="password" name="password" required class="fb-input" placeholder="••••••••">
        </div>
        <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 text-[13.5px] text-ios-gray cursor-pointer select-none">
                <input type="checkbox" name="remember" class="accent-ios-blue w-4 h-4"> Ingat saya
            </label>
        </div>
        <button type="submit" class="fb-btn fb-btn-primary w-full !py-4">Masuk</button>
    </form>

    <div class="mt-6 pt-5 border-t border-black/5 text-center text-[13.5px] text-ios-gray">
        Belum punya akun? <a href="{{ route('register') }}" class="text-ios-blue font-semibold">Daftar Sekarang</a>
    </div>

    <div class="mt-4 bg-ios-gray rounded-xl p-3 text-[12px] text-ios-gray leading-relaxed">
        <b class="text-ios-label">Akun demo:</b> company@freebuff.test / password — Super Admin: super@freebuff.test / password
    </div>
@endsection