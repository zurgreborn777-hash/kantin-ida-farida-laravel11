<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="https://down-id.img.susercontent.com/file/id-11134207-7rbk9-mam5uqozn7x508">
    <title>Kantin Ibu Ida - Nasi Rames Terbaik</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;1,700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (() => {
            const savedTheme = localStorage.getItem('kantin-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            const theme = savedTheme || systemTheme;
            const reduceMotion = localStorage.getItem('kantin-reduce-motion') === 'true';
            document.documentElement.classList.toggle('theme-light', theme === 'light');
            document.documentElement.classList.toggle('theme-dark', theme === 'dark');
            document.documentElement.classList.toggle('reduce-motion', reduceMotion);
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('preferences', {
                theme: localStorage.getItem('kantin-theme') || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark'),
                reduceMotion: localStorage.getItem('kantin-reduce-motion') === 'true',
                init() {
                    this.apply();
                    window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', (event) => {
                        if (localStorage.getItem('kantin-theme')) return;
                        this.theme = event.matches ? 'light' : 'dark';
                        this.apply();
                    });
                },
                setTheme(theme) {
                    this.theme = theme;
                    localStorage.setItem('kantin-theme', theme);
                    this.apply();
                },
                toggleReduceMotion() {
                    this.reduceMotion = !this.reduceMotion;
                    localStorage.setItem('kantin-reduce-motion', this.reduceMotion ? 'true' : 'false');
                    this.apply();
                },
                apply() {
                    document.documentElement.classList.toggle('theme-light', this.theme === 'light');
                    document.documentElement.classList.toggle('theme-dark', this.theme === 'dark');
                    document.documentElement.classList.toggle('reduce-motion', this.reduceMotion);
                }
            });
            Alpine.store('preferences').init();

            Alpine.store('cart', {
                @auth
                @php
                    $activeOrder = \App\Models\Order::where('user_id', auth()->id())
                        ->where('status', 'pending')
                        ->where(function($q) {
                            $q->whereNull('location')->orWhere('location', 'not like', 'Kasir - %');
                        })->first();
                    $cartCount = $activeOrder ? $activeOrder->items()->sum('quantity') : 0;
                @endphp
                count: {{ $cartCount }},
                @else
                count: 0,
                @endauth
                updateCount(newCount) {
                    this.count = newCount;
                }
            });
        });
    </script>
</head>
<body class="@yield('body_class')">
    @auth
        @if(!auth()->user()->is_admin)
        <button class="kitchen-backdrop" type="button" onclick="document.body.classList.remove('kitchen-is-open')" aria-label="Tutup panel pesanan"></button>
        <aside class="kitchen-panel">
            <div class="kitchen-profile">
                <span><i class="fa-solid fa-user"></i></span>
                <div>
                    <strong>Dapur Anda</strong>
                    <small>Layanan Premium</small>
                </div>
                <button class="kitchen-close" type="button" onclick="document.body.classList.remove('kitchen-is-open')" aria-label="Tutup panel pesanan">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <nav class="kitchen-menu" aria-label="Menu pesanan">
                <a href="{{ route('cart') }}" @if(request()->routeIs('cart')) class="active" @endif><i class="fa-solid fa-receipt"></i> Pesanan Aktif</a>
                <a href="{{ route('orders.my') }}" @if(request()->routeIs('orders.*')) class="active" @endif><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Pesanan</a>
                <a href="{{ route('menu') }}" @if(request()->routeIs('menu*')) class="active" @endif><i class="fa-regular fa-heart"></i> Menu Favorit</a>
                <a href="{{ route('cart') }}"><i class="fa-regular fa-credit-card"></i> Metode Pembayaran</a>
                <a href="{{ route('profile') }}" @if(request()->routeIs('profile*')) class="active" @endif><i class="fa-solid fa-gear"></i> Pengaturan</a>
            </nav>
            <a class="kitchen-checkout" href="{{ route('cart') }}">Checkout Sekarang</a>
        </aside>
        @endif
    @endauth

    <nav class="navbar animate-fade-in-up">
        <div class="container">
            <a href="/" class="navbar-brand">
                <i class="fa-solid fa-bowl-rice" style="color: var(--primary)"></i> 
                Kantin<span>IbuIda</span>
            </a>
            
            <div class="nav-links">
                <a href="{{ route('home') }}" class="nav-link">Beranda</a>
                <a href="{{ route('menu') }}" class="nav-link">Menu</a>
                
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="/admin" class="nav-link">Panel Admin</a>
                    @endif

                    
                    <a href="{{ route('cart') }}" class="nav-link" style="position: relative;" x-data>
                        <i class="fa-solid fa-cart-shopping"></i> Keranjang
                        <span class="badge" x-show="$store.cart.count > 0" x-text="$store.cart.count"></span>
                    </a>

                    <div class="account-menu" x-data="{ open: false }">
                        <button @click="open = !open" class="btn btn-outline account-trigger" type="button">
                            <i class="fa-solid fa-user"></i> {{ auth()->user()->name }}
                        </button>
                        <div x-show="open" @click.away="open = false" class="account-dropdown" style="display:none;" :style="{display: open ? 'grid' : 'none'}">
                            <a href="{{ route('profile') }}" class="account-dropdown-link">Profil Saya</a>
                            <a href="{{ route('orders.my') }}" class="account-dropdown-link">Pesanan Saya</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="account-dropdown-logout">Keluar</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Masuk</a>
                @endauth
            </div>
            @auth
                @if(!auth()->user()->is_admin)
                    <button class="kitchen-toggle" type="button" onclick="document.body.classList.toggle('kitchen-is-open')" aria-label="Buka panel pesanan">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                @endif
            @endauth
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <a href="{{ route('home') }}" class="navbar-brand">
                    <i class="fa-solid fa-bowl-rice" style="color: var(--primary)"></i>
                    Kantin<span>IbuIda</span>
                </a>
                <p>
                    Nasi rames rumahan, pembayaran aman, dan pengantaran area kantin
                    maksimal 2 KM. Dibuat untuk makan harian yang praktis.
                </p>
                <div class="footer-socials" aria-label="Kontak Kantin">
                    <span><i class="fa-solid fa-location-dot"></i></span>
                    <span><i class="fa-solid fa-phone"></i></span>
                    <span><i class="fa-solid fa-receipt"></i></span>
                </div>
            </div>

            <div class="footer-panel">
                <h4>Navigasi</h4>
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('menu') }}">Menu</a>
                @auth
                    <a href="{{ route('orders.my') }}">Pesanan Saya</a>
                    <a href="{{ route('cart') }}">Keranjang</a>
                @else
                    <a href="{{ route('login') }}">Masuk</a>
                @endauth
            </div>

            <div class="footer-panel">
                <h4>Layanan</h4>
                <div class="footer-service">
                    <i class="fa-solid fa-motorcycle"></i>
                    <span>Antar sekitar kantin maksimal 2 KM.</span>
                </div>
                <div class="footer-service">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>Pembayaran digital via Duitku sandbox.</span>
                </div>
                <div class="footer-service">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Nota tersedia setelah pesanan dibuat.</span>
                </div>
            </div>
        </div>

        <div class="container footer-bottom">
            <span>&copy; {{ date('Y') }} Kantin Ibu Ida. Hak cipta dilindungi.</span>
            <span>Makanan segar, checkout sederhana, dan pengujian Railway yang nyata.</span>
        </div>
    </footer>

    <!-- Theme Toggle Button (Fixed, bottom-left) -->
    <button
        id="theme-toggle-btn"
        aria-label="Toggle dark/light mode"
        title="Ganti Mode Terang/Gelap"
        onclick="
            const store = Alpine.store('preferences');
            const newTheme = store.theme === 'dark' ? 'light' : 'dark';
            store.setTheme(newTheme);
        "
    >
        <i class="fa-solid fa-moon" x-data x-show="$store.preferences.theme === 'light'" style="display:none;"></i>
        <i class="fa-solid fa-sun" x-data x-show="$store.preferences.theme === 'dark'" style="display:none;"></i>
    </button>
    <style>
        #theme-toggle-btn {
            position: fixed;
            bottom: 24px;
            left: 24px;
            z-index: 9999;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: 2px solid var(--border-color, #333);
            background: var(--bg-card, #1a1a2e);
            color: var(--text-primary, #fff);
            font-size: 1.3rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(0,0,0,0.4);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.3s ease;
        }
        #theme-toggle-btn:hover {
            transform: scale(1.15);
            box-shadow: 0 6px 20px rgba(0,0,0,0.5);
        }
        #theme-toggle-btn:active {
            transform: scale(0.95);
        }
    </style>
</body>
</html>
