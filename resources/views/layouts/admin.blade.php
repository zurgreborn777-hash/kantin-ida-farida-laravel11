<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="https://down-id.img.susercontent.com/file/id-11134207-7rbk9-mam5uqozn7x508">
    <title>Admin - Kantin Ibu Ida</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (() => {
            const savedTheme = localStorage.getItem('kantin-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            const theme = savedTheme || systemTheme;
            document.documentElement.classList.toggle('theme-light', theme === 'light');
            document.documentElement.classList.toggle('theme-dark', theme === 'dark');
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('preferences', {
                theme: localStorage.getItem('kantin-theme') || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark'),
                init() {
                    this.apply();
                },
                setTheme(theme) {
                    this.theme = theme;
                    localStorage.setItem('kantin-theme', theme);
                    this.apply();
                },
                apply() {
                    document.documentElement.classList.toggle('theme-light', this.theme === 'light');
                    document.documentElement.classList.toggle('theme-dark', this.theme === 'dark');
                }
            });
            Alpine.store('preferences').init();
        });
    </script>
</head>
<body class="admin-dashboard">
    
    <aside class="admin-sidebar animate-fade-in-up">
        <a href="{{ route('home') }}" class="navbar-brand mb-4" style="display:block; text-align:center;">
            Kantin<span>IbuIda</span>
        </a>
        <div class="mt-4">
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->is('admin') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Dasbor
            </a>
            <a href="{{ route('admin.menus') }}" class="nav-item {{ request()->is('admin/menus*') ? 'active' : '' }}">
                <i class="fa-solid fa-burger"></i> Menu
            </a>
            <a href="{{ route('admin.orders') }}" class="nav-item {{ request()->is('admin/orders*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt"></i> Pesanan
            </a>
            <a href="{{ route('admin.users') }}" class="nav-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> Pengguna
            </a>
            <a href="{{ route('admin.kasir') }}" class="nav-item {{ request()->is('admin/kasir*') ? 'active' : '' }}">
                <i class="fa-solid fa-cash-register"></i> Kasir
            </a>

        </div>
        
        <div style="position:absolute; bottom:2rem; width:calc(100% - 4rem);">
            <div style="margin-bottom: 0.5rem; text-align: center;">
                <button type="button" style="background:none;border:1px solid var(--border-color,#444);color:var(--text-primary,#fff);padding:6px 12px;border-radius:8px;cursor:pointer;" onclick="const s=Alpine.store('preferences');s.setTheme(s.theme==='dark'?'light':'dark');">
                    <i class="fa-solid fa-moon" x-data x-show="$store.preferences.theme === 'light'" style="display:none;"></i>
                    <i class="fa-solid fa-sun" x-data x-show="$store.preferences.theme === 'dark'" style="display:none;"></i>
                    <span style="font-size:0.8rem; margin-left:4px;">Toggle Theme</span>
                </button>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline" style="width:100%"><i class="fa-solid fa-sign-out"></i> Keluar</button>
            </form>
        </div>
    </aside>

    <main class="admin-content animate-fade-in-up delay-100">
        <div class="flex justify-between items-center mb-4">
            <h2>@yield('title', 'Dasbor')</h2>
            <div class="flex items-center gap-1">
                <span style="font-weight:bold;">Admin {{ auth()->user()->name }}</span>
            </div>
        </div>
        @yield('content')
    </main>

</body>
</html>
