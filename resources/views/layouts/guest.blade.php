<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Freebuff') — Freebuff Marketing Suite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://unpkg.com/lucide@0.424.0/dist/umd/lucide.min.js"></script>
</head>
<body class="fb-auth-bg">
    <div class="min-h-screen flex flex-col items-center justify-center px-5 py-10">
        <div class="w-full max-w-[420px] fb-fade-up">
            <div class="flex flex-col items-center mb-8">
                <div class="w-16 h-16 rounded-[20px] bg-ios-blue flex items-center justify-center text-white font-black text-3xl shadow-xl shadow-ios-blue/40 mb-4">F</div>
                <h1 class="text-[28px] font-extrabold tracking-tight">Freebuff</h1>
                <p class="text-ios-gray text-[15px] mt-1">Marketing Suite — Konten, Insight & Otomasi</p>
            </div>

            <div class="fb-card fb-card-pad p-7 shadow-xl">
                @yield('content')
            </div>

            <p class="text-center text-[12px] text-ios-gray mt-6">
                Freebuff · Social Media & Digital Marketing Tools
            </p>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () { if (window.lucide) lucide.createIcons(); });
    </script>
</body>
</html>