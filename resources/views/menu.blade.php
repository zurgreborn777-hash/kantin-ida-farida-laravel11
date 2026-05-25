@extends('layouts.app')

@section('body_class', 'home-dashboard')

@section('content')
<section class="menu-page" x-data="{ filter: 'Semua', search: '' }">
    <div class="dashboard-container">
        <div class="menu-hero animate-fade-in-up">
            <div>
                <h1>Rasa Rumahan Pilihan</h1>
                <p>Menu autentik yang diracik telaten dengan sentuhan modern, langsung dari dapur Ibu Ida ke meja makanmu.</p>
            </div>

            <label class="menu-search" aria-label="Cari menu">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" x-model="search" placeholder="Cari menu favoritmu...">
            </label>
        </div>

        <div class="menu-filter-bar animate-fade-in-up">
            <button @click="filter = 'Semua'" :class="{ 'active': filter === 'Semua' }" type="button">Semua</button>
            <button @click="filter = 'Makanan'" :class="{ 'active': filter === 'Makanan' }" type="button">Makanan</button>
            <button @click="filter = 'Minuman'" :class="{ 'active': filter === 'Minuman' }" type="button">Minuman</button>
        </div>

        @if(session('success'))
            <div class="menu-alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="menu-catalog animate-fade-in-up delay-100">
            @foreach($menus as $menu)
            <article
                class="menu-showcase-card"
                x-show="(filter === 'Semua' || filter === '{{ $menu->category }}') && '{{ strtolower(addslashes($menu->name . ' ' . $menu->description)) }}'.includes(search.toLowerCase())"
                x-transition.opacity
            >
                <a href="{{ route('menu.show', $menu) }}" class="menu-card-image-link" aria-label="Lihat detail {{ $menu->name }}">
                    <img src="{{ $menu->image_url ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=900&auto=format&fit=crop' }}" alt="{{ $menu->name }}">
                </a>

                <div class="menu-card-body">
                    <div class="menu-title-row">
                        <h2><a href="{{ route('menu.show', $menu) }}">{{ $menu->name }}</a></h2>
                        <span>Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                    </div>

                    <p>{{ $menu->description ?: 'Menu rumahan pilihan Kantin Ibu Ida, dimasak segar untuk pesanan harian.' }}</p>

                    <div class="menu-card-footer">
                        <small>Sisa Stok: {{ $menu->stock }}</small>

                        @auth
                        <div class="menu-order-control" x-data="{
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
                            @if($menu->stock > 0)
                                <input type="number" x-model="quantity" min="1" max="{{ $menu->stock }}" aria-label="Jumlah {{ $menu->name }}">
                                <button @click="addToCart()" type="button">
                                    <i class="fa-solid fa-cart-plus"></i>
                                    <span>Tambah</span>
                                </button>
                            @else
                                <button type="button" disabled>Stok Habis</button>
                            @endif
                        </div>
                        @else
                            <a href="{{ route('login') }}" class="menu-login-link">Masuk untuk Pesan</a>
                        @endauth
                        <a href="{{ route('menu.show', $menu) }}" class="menu-detail-link">Lihat Detail</a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
