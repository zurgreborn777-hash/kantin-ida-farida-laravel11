<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Kantin Ibu Ida</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body style="background: var(--background);">
    
    <aside class="admin-sidebar animate-fade-in-up">
        <a href="/" class="navbar-brand mb-4" style="display:block; text-align:center;">
            Kantin<span>IbuIda</span>
        </a>
        <div class="mt-4">
            <a href="/admin" class="nav-item {{ request()->is('admin') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
            <a href="/admin/menus" class="nav-item {{ request()->is('admin/menus*') ? 'active' : '' }}">
                <i class="fa-solid fa-burger"></i> Menus
            </a>
            <a href="/admin/orders" class="nav-item {{ request()->is('admin/orders*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt"></i> Orders
            </a>
            <a href="/admin/users" class="nav-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> Users
            </a>
            <a href="/admin/kasir" class="nav-item {{ request()->is('admin/kasir*') ? 'active' : '' }}">
                <i class="fa-solid fa-cash-register"></i> Kasir
            </a>

        </div>
        
        <div style="position:absolute; bottom:2rem; width:calc(100% - 4rem);">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline" style="width:100%"><i class="fa-solid fa-sign-out"></i> Logout</button>
            </form>
        </div>
    </aside>

    <main class="admin-content animate-fade-in-up delay-100">
        <div class="flex justify-between items-center mb-4">
            <h2>@yield('title', 'Dashboard')</h2>
            <div class="flex items-center gap-1">
                <span style="font-weight:bold;">Admin {{ auth()->user()->name }}</span>
            </div>
        </div>
        @yield('content')
    </main>

</body>
</html>
