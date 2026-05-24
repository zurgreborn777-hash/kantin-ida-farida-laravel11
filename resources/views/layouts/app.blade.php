<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kantin Ibu Ida - Nasi Rames Terbaik</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('alpine:init', () => {
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
<body>
    
    <nav class="navbar animate-fade-in-up">
        <div class="container">
            <a href="/" class="navbar-brand">
                <i class="fa-solid fa-bowl-rice" style="color: var(--primary)"></i> 
                Kantin<span>IbuIda</span>
            </a>
            
            <div class="nav-links">
                <a href="{{ route('home') }}" class="nav-link">Home</a>
                <a href="{{ route('menu') }}" class="nav-link">Menu</a>
                
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="/admin" class="nav-link">Admin Panel</a>
                    @endif

                    
                    <a href="{{ route('cart') }}" class="nav-link" style="position: relative;" x-data>
                        <i class="fa-solid fa-cart-shopping"></i> Keranjang
                        <span class="badge" x-show="$store.cart.count > 0" x-text="$store.cart.count"></span>
                    </a>

                    <div style="position:relative; margin-left: 0.5rem;" x-data="{ open: false }">
                        <button @click="open = !open" class="btn btn-outline" style="padding: 0.5rem 1rem;">
                            <i class="fa-solid fa-user"></i> {{ auth()->user()->name }}
                        </button>
                        <div x-show="open" @click.away="open = false" style="position:absolute; top:120%; right:0; background:white; padding:1rem; border-radius:var(--radius-md); box-shadow:var(--shadow-strong); min-width: 200px; display:none;" :style="{display: open ? 'block' : 'none'}">
                            <a href="{{ route('profile') }}" class="btn btn-outline" style="width:100%; margin-bottom: 0.5rem; justify-content: center;">Profil Saya</a>
                            <a href="{{ route('orders.my') }}" class="btn btn-outline" style="width:100%; margin-bottom: 0.5rem; justify-content: center;">Pesanan Saya</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="width:100%">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                @endauth
            </div>
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
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('menu') }}">Menu</a>
                @auth
                    <a href="{{ route('orders.my') }}">Pesanan Saya</a>
                    <a href="{{ route('cart') }}">Keranjang</a>
                @else
                    <a href="{{ route('login') }}">Login</a>
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
                    <span>Invoice tersedia setelah pesanan dibuat.</span>
                </div>
            </div>
        </div>

        <div class="container footer-bottom">
            <span>&copy; {{ date('Y') }} Kantin Ibu Ida. All rights reserved.</span>
            <span>Fresh food, simple checkout, real Railway testing.</span>
        </div>
    </footer>

</body>
</html>
