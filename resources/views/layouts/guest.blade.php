<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TaskFlow') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes blobFloatA {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%      { transform: translate(40px, 30px) scale(1.08); }
            66%      { transform: translate(-20px, 50px) scale(0.95); }
        }
        @keyframes blobFloatB {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(-50px, -30px) scale(1.12); }
        }
        @keyframes blobFloatC {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25%      { transform: translate(60px, -40px) scale(1.1); }
            75%      { transform: translate(-30px, 40px) scale(0.9); }
        }
        @keyframes auroraShift {
            0%, 100% { background-position: 0% 50%; }
            50%      { background-position: 100% 50%; }
        }
        .auth-bg {
            background: linear-gradient(135deg, #ECFDF5 0%, #F0FDF4 25%, #CCFBF1 50%, #F0FDF4 75%, #ECFDF5 100%);
            background-size: 400% 400%;
            animation: auroraShift 18s ease-in-out infinite;
        }
        .blob-a { animation: blobFloatA 14s ease-in-out infinite; }
        .blob-b { animation: blobFloatB 18s ease-in-out infinite; }
        .blob-c { animation: blobFloatC 22s ease-in-out infinite; }

        /* Inner animated background (inside the auth card) */
        @keyframes cardAurora {
            0%, 100% { background-position: 0% 50%; }
            50%      { background-position: 100% 50%; }
        }
        @keyframes innerBlob1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(40px, 30px) scale(1.15); }
        }
        @keyframes innerBlob2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(-35px, -25px) scale(1.1); }
        }
        .card-bg {
            position: absolute; inset: 0;
            background: linear-gradient(135deg, #FFFFFF 0%, #F0FDF4 50%, #ECFDF5 100%);
            background-size: 250% 250%;
            animation: cardAurora 14s ease-in-out infinite;
            border-radius: inherit;
            z-index: 0;
        }
        .card-blob-1, .card-blob-2 {
            position: absolute; border-radius: 9999px;
            filter: blur(40px); pointer-events: none; z-index: 0;
        }
        .card-blob-1 {
            top: -40px; right: -40px; width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(110,231,183,0.45), transparent 70%);
            animation: innerBlob1 12s ease-in-out infinite;
        }
        .card-blob-2 {
            bottom: -30px; left: -30px; width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(20,184,166,0.30), transparent 70%);
            animation: innerBlob2 16s ease-in-out infinite;
        }
        .card-content { position: relative; z-index: 1; }

        @media (prefers-reduced-motion: reduce) {
            .auth-bg, .blob-a, .blob-b, .blob-c,
            .card-bg, .card-blob-1, .card-blob-2 { animation: none; }
        }
    </style>
</head>
<body class="font-sans text-mint-900 antialiased">
    <div class="auth-bg relative min-h-screen flex items-center justify-center px-5 py-10 overflow-hidden">
        {{-- Animated decorative blobs --}}
        <div class="blob-a pointer-events-none absolute -top-20 -right-20 w-[400px] h-[400px] rounded-full blur-2xl"
             style="background: radial-gradient(circle, rgba(16,185,129,0.18), transparent 70%);"></div>
        <div class="blob-b pointer-events-none absolute -bottom-16 -left-16 w-[300px] h-[300px] rounded-full blur-2xl"
             style="background: radial-gradient(circle, rgba(20,184,166,0.16), transparent 70%);"></div>
        <div class="blob-c pointer-events-none absolute top-[40%] left-[10%] w-[200px] h-[200px] rounded-full blur-2xl"
             style="background: radial-gradient(circle, rgba(168,85,247,0.10), transparent 70%);"></div>

        <div class="relative z-10 w-full max-w-[460px] rounded-3xl p-8 sm:p-12 shadow-mint-lg overflow-hidden bg-white">
            <div class="card-bg"></div>
            <div class="card-blob-1"></div>
            <div class="card-blob-2"></div>
            <div class="card-content">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
