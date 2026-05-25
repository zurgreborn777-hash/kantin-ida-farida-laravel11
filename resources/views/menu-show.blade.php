@extends('layouts.app')

@section('body_class', 'home-dashboard')

@section('content')
<section class="menu-detail-page" x-data="{
    quantity: 1,
    async addToCart() {
        if (this.quantity > {{ $menu->stock }}) {
            alert('Stok tidak mencukupi! Sisa: {{ $menu->stock }}');
            return;
        }

        const response = await fetch('{{ route('order.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                menu_id: {{ $menu->id }},
                quantity: this.quantity
            })
        });

        const data = await response.json();
        if (data.success) {
            $store.cart.updateCount(data.cartCount);
            alert('Berhasil ditambahkan ke keranjang!');
        } else {
            alert(data.message || 'Gagal menambahkan pesanan');
        }
    }
}">
    <div class="menu-detail-shell dashboard-container">
        <div class="menu-detail-media animate-fade-in-up">
            <img src="{{ $menu->image_url ?? 'https://images.unsplash.com/photo-1512058564366-18510be2db19?q=80&w=1200&auto=format&fit=crop' }}" alt="{{ $menu->name }}">
            <div class="menu-detail-overlay">
                <span>{{ $menu->category ?: 'Menu Ibu Ida' }}</span>
                <div>
                    <h2>{{ $menu->name }}</h2>
                    <strong>Rp {{ number_format($menu->price, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="menu-detail-copy animate-fade-in-up delay-100">
            <div class="menu-detail-meta">
                <span>{{ $menu->category ?: 'Hidangan Andalan' }}</span>
                <small><i class="fa-solid fa-star"></i> 4.9 ({{ max(24, $menu->id * 17) }} ulasan)</small>
            </div>

            <h1>{{ $menu->name }}</h1>
            <p class="menu-detail-description">
                {{ $menu->description ?: 'Hidangan rumahan khas Kantin Ibu Ida, dimasak segar setiap hari dengan rasa autentik dan porsi yang pas untuk makan harian.' }}
            </p>

            <div class="menu-detail-info-grid">
                <div class="menu-detail-info">
                    <h3>Profil Hidangan</h3>
                    <dl>
                        <div>
                            <dt>Kategori</dt>
                            <dd>{{ $menu->category ?: 'Menu Harian' }}</dd>
                        </div>
                        <div>
                            <dt>Stok</dt>
                            <dd>{{ $menu->stock }} porsi</dd>
                        </div>
                        <div>
                            <dt>Harga</dt>
                            <dd>Rp {{ number_format($menu->price, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="menu-detail-info">
                    <h3>Catatan Dapur</h3>
                    <ul>
                        <li>Dimasak segar dari dapur Kantin Ibu Ida</li>
                        <li>Cocok untuk makan siang dan pesanan harian</li>
                        <li>Stok mengikuti ketersediaan menu hari ini</li>
                    </ul>
                </div>
            </div>

            <div class="menu-detail-actions">
                <div class="menu-detail-qty">
                    <button type="button" @click="quantity = Math.max(1, quantity - 1)">-</button>
                    <input type="number" x-model="quantity" min="1" max="{{ $menu->stock }}" aria-label="Jumlah {{ $menu->name }}">
                    <button type="button" @click="quantity = Math.min({{ max(1, $menu->stock) }}, quantity + 1)">+</button>
                </div>

                @auth
                    @if($menu->stock > 0)
                        <button type="button" class="menu-detail-cart" @click="addToCart()">
                            <i class="fa-solid fa-basket-shopping"></i>
                            <span>Tambah ke Keranjang - Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                        </button>
                    @else
                        <button type="button" class="menu-detail-cart" disabled>Stok Habis</button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="menu-detail-cart">
                        <i class="fa-solid fa-user"></i>
                        <span>Masuk untuk Pesan</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</section>
@endsection
