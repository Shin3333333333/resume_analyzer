<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeVault — Your Personal Space</title>
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,300;0,6..72,400;1,6..72,300;1,6..72,400&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

    <div id="loading">
        <div class="loader-logo">LifeVault</div>
        <div class="loader-bar"></div>
    </div>

    <div id="auth-screen">
        <div class="auth-card">
            <div class="auth-logo">LifeVault</div>
            <p class="auth-tagline">Your private, encrypted, personal space.</p>
            <div class="auth-features">
                <div class="auth-feature"><div class="auth-feature-icon" style="background:rgba(167,139,250,.15);color:var(--lavender)">📓</div> Journal everything</div>
                <div class="auth-feature"><div class="auth-feature-icon" style="background:rgba(52,211,153,.15);color:var(--green)">✅</div> Manage your tasks</div>
                <div class="auth-feature"><div class="auth-feature-icon" style="background:rgba(251,191,36,.15);color:var(--amber)">🎯</div> Track your goals</div>
            </div>
            <button class="google-btn" id="google-login-btn">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" class="google-icon">
                Sign in with Google
            </button>
            <p class="privacy-note">Your data is yours. We'll never read or sell it.</p>
        </div>
    </div>

    <div id="app">
        @include('layouts.partials._sidebar')
        <div id="sidebar-overlay" class="sidebar-overlay" onclick="closeSidebar()"></div>

        <div class="main">
            @include('dashboard')
            @include('journal')
            @include('tasks')
            @include('goals')
            @include('insights')
            @include('community')
            @include('leaderboard')
            @include('settings')
        </div>
    </div>

    @include('layouts.partials.modals')
    @include('layouts.partials._toast')
    @include('layouts.partials._journal-expand')

    <script src="{{ asset('js/app.js') }}" type="module"></script>

</body>
</html>