<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Freebuff') — Freebuff Marketing Suite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ios: { blue: '#0A84FF', green: '#34C759', red: '#FF3B30', orange: '#FF9500', purple: '#AF52DE', gray: '#F2F2F7', label: '#1C1C1E' },
                    },
                    fontFamily: { sans: ['-apple-system', 'BlinkMacSystemFont', 'SF Pro Display', 'SF Pro Text', 'Segoe UI', 'Roboto', 'Inter', 'sans-serif'] },
                },
            },
        };
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@0.424.0/dist/umd/lucide.min.js"></script>
</head>
<body>
    <div class="flex">
        <!-- ================= Sidebar ================= -->
        <aside class="fb-sidebar">
            <div class="flex items-center gap-3 px-6 pt-6 pb-2">
                <div class="w-10 h-10 rounded-[13px] bg-ios-blue flex items-center justify-center text-white font-black text-lg shadow-lg shadow-ios-blue/30">F</div>
                <div>
                    <div class="font-extrabold text-[17px] tracking-tight leading-none">Freebuff</div>
                    <div class="text-[11px] text-ios-gray mt-1 font-medium">Marketing Suite</div>
                </div>
            </div>

            @auth
            <nav class="flex-1 overflow-y-auto pb-4">
                @if(auth()->user()->isSuper())
                <div class="fb-nav-group">
                    <div class="fb-nav-group-label">Super Admin</div>
                    <a href="{{ route('super.index') }}" class="fb-nav-item {{ request()->routeIs('super.index') ? 'active' : '' }}">
                        <i data-lucide="shield-check" class="w-[18px] h-[18px]"></i> Approval
                        @php $pendingCount = \App\Models\Content::where('status','pending')->count(); @endphp
                        @if($pendingCount > 0)<span class="badge-dot">{{ $pendingCount }}</span>@endif
                    </a>
                    <a href="{{ route('super.companies') }}" class="fb-nav-item {{ request()->routeIs('super.companies') ? 'active' : '' }}">
                        <i data-lucide="building-2" class="w-[18px] h-[18px]"></i> Perusahaan
                    </a>
                </div>
                @else
                <div class="fb-nav-group">
                    <div class="fb-nav-group-label">Utama</div>
                    <a href="{{ route('dashboard') }}" class="fb-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard" class="w-[18px] h-[18px]"></i> Dashboard
                    </a>
                </div>

                <div class="fb-nav-group">
                    <div class="fb-nav-group-label">Produksi Konten</div>
                    <a href="{{ route('tools.content.index') }}" class="fb-nav-item {{ request()->routeIs('tools.content.*') ? 'active' : '' }}">
                        <i data-lucide="palette" class="w-[18px] h-[18px]"></i> Content Studio
                    </a>
                </div>

                <div class="fb-nav-group">
                    <div class="fb-nav-group-label">Analytics</div>
                    <a href="{{ route('tools.engagement.index') }}" class="fb-nav-item {{ request()->routeIs('tools.engagement.*') ? 'active' : '' }}">
                        <i data-lucide="heart-handshake" class="w-[18px] h-[18px]"></i> Engagement Rate
                    </a>
                    <a href="{{ route('tools.meta.index') }}" class="fb-nav-item {{ request()->routeIs('tools.meta.*') ? 'active' : '' }}">
                        <i data-lucide="facebook" class="w-[18px] h-[18px]"></i> Meta Insights
                    </a>
                    <a href="{{ route('tools.google-ads.index') }}" class="fb-nav-item {{ request()->routeIs('tools.google-ads.*') ? 'active' : '' }}">
                        <i data-lucide="search" class="w-[18px] h-[18px]"></i> Google Ads
                    </a>
                    <a href="{{ route('tools.analytics.index') }}" class="fb-nav-item {{ request()->routeIs('tools.analytics.*') ? 'active' : '' }}">
                        <i data-lucide="bar-chart-3" class="w-[18px] h-[18px]"></i> Google Analytics
                    </a>
                </div>

                <div class="fb-nav-group">
                    <div class="fb-nav-group-label">Lainnya</div>
                    <a href="{{ route('settings.index') }}" class="fb-nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i data-lucide="settings" class="w-[18px] h-[18px]"></i> Settings
                    </a>
                </div>
                @endif
            </nav>
            @endauth

            <div class="p-4 border-t border-black/5">
                @auth
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()->avatarUrl() }}" class="fb-avatar" alt="">
                    <div class="min-w-0 flex-1">
                        <div class="text-[13.5px] font-semibold truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11.5px] text-ios-gray truncate">{{ auth()->user()->isSuper() ? 'Super Admin' : optional(auth()->user()->company)->name }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="fb-editor-tool" title="Keluar">
                            <i data-lucide="log-out" class="w-[18px] h-[18px]"></i>
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </aside>

        <!-- ================= Main ================= -->
        <div class="fb-main flex-1">
            <header class="fb-topbar fb-glass">
                <div class="text-[13px] font-medium text-ios-gray hidden md:block">{{ auth()->user()->isSuper() ? 'Super Admin Console' : optional(auth()->user()->company)->name }}</div>
                <div class="flex-1"></div>
                <div class="hidden md:flex items-center gap-2">
                    @auth
                    @if(!auth()->user()->isSuper())
                    <a href="{{ route('tools.content.create') }}" class="fb-btn fb-btn-primary fb-btn-sm">
                        <i data-lucide="plus" class="w-4 h-4"></i> Konten Baru
                    </a>
                    @endif
                    @endauth
                </div>
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()->avatarUrl() }}" class="fb-avatar md:hidden" alt="">
                    <form method="POST" action="{{ route('logout') }}" class="md:hidden">
                        @csrf
                        <button type="submit" class="fb-editor-tool text-ios-gray"><i data-lucide="log-out" class="w-[20px] h-[20px]"></i></button>
                    </form>
                </div>
            </header>

            <main class="fb-content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- ================= Mobile Tabbar ================= -->
    @auth
    @if(!auth()->user()->isSuper())
    <nav class="fb-tabbar">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard" class="w-[22px] h-[22px]"></i> Home
        </a>
        <a href="{{ route('tools.content.index') }}" class="{{ request()->routeIs('tools.content.*') ? 'active' : '' }}">
            <i data-lucide="palette" class="w-[22px] h-[22px]"></i> Studio
        </a>
        <a href="{{ route('tools.engagement.index') }}" class="{{ request()->routeIs('tools.engagement.*') ? 'active' : '' }}">
            <i data-lucide="heart-handshake" class="w-[22px] h-[22px]"></i> ER
        </a>
        <a href="{{ route('tools.meta.index') }}" class="{{ request()->routeIs('tools.meta.*') ? 'active' : '' }}">
            <i data-lucide="facebook" class="w-[22px] h-[22px]"></i> Meta
        </a>
        <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <i data-lucide="settings" class="w-[22px] h-[22px]"></i> Settings
        </a>
    </nav>
    @else
    <nav class="fb-tabbar">
        <a href="{{ route('super.index') }}" class="{{ request()->routeIs('super.index') ? 'active' : '' }}">
            <i data-lucide="shield-check" class="w-[22px] h-[22px]"></i> Approval
        </a>
        <a href="{{ route('super.companies') }}" class="{{ request()->routeIs('super.companies') ? 'active' : '' }}">
            <i data-lucide="building-2" class="w-[22px] h-[22px]"></i> Perusahaan
        </a>
    </nav>
    @endif
    @endauth

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) lucide.createIcons();
            // Flash message -> toast
            @if(session('success'))
                FB.toast(@json(session('success')), 'success');
            @endif
            @if(session('error'))
                FB.toast(@json(session('error')), 'error');
            @endif
            @if(session('last_result'))
                var r = @json(session('last_result'));
                FB.toast('Engagement Rate: ' + r.rate + '% (' + r.grade + ')', 'success');
            @endif
        });
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>