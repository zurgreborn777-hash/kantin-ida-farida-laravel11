@extends('layouts.app')

@section('body_class', 'home-dashboard')

@section('content')
<div class="dashboard-page" x-data="{ kitchenOpen: false }" :class="{ 'kitchen-is-open': kitchenOpen }">
    <button class="kitchen-toggle" type="button" @click="kitchenOpen = true" aria-label="Buka panel pesanan">
        <i class="fa-solid fa-bars"></i>
    </button>
    <button class="kitchen-backdrop" type="button" @click="kitchenOpen = false" aria-label="Tutup panel pesanan"></button>

    <aside class="kitchen-panel" :aria-hidden="!kitchenOpen">
        <div class="kitchen-profile">
            <span><i class="fa-solid fa-user"></i></span>
            <div>
                <strong>Dapur Anda</strong>
                <small>Layanan Premium</small>
            </div>
            <button class="kitchen-close" type="button" @click="kitchenOpen = false" aria-label="Tutup panel pesanan">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="kitchen-menu" aria-label="Menu pesanan">
            <a class="active" href="{{ route('cart') }}"><i class="fa-solid fa-receipt"></i> Pesanan Aktif</a>
            <a href="{{ route('orders.my') }}"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Pesanan</a>
            <a href="{{ route('menu') }}"><i class="fa-regular fa-heart"></i> Menu Favorit</a>
            <a href="{{ route('cart') }}"><i class="fa-regular fa-credit-card"></i> Metode Pembayaran</a>
            <a href="{{ route('profile') }}"><i class="fa-solid fa-gear"></i> Pengaturan</a>
        </nav>

        <a class="kitchen-checkout" href="{{ route('cart') }}">Checkout Sekarang</a>
    </aside>

    <section class="artisan-hero">
        <div class="dashboard-container">
            <span class="artisan-pill">Pilihan Hari Ini</span>
            <h1>Hidangan Andalan<br><em>Nasi Rames</em></h1>
            <p>Racikan resep warisan yang dimasak perlahan di dapur Ibu Ida. Rasa rumahan, disajikan lebih istimewa.</p>
            <div class="artisan-actions">
                <a href="{{ route('menu') }}" class="artisan-btn artisan-btn-primary">Pesan Sekarang</a>
                <a href="{{ route('menu') }}" class="artisan-btn artisan-btn-ghost">Lihat Menu</a>
            </div>
        </div>
    </section>

    <section id="about" class="story-section dashboard-container">
        <img src="https://images.unsplash.com/photo-1551218808-94e220e084d2?q=80&w=1200&auto=format&fit=crop" alt="Nasi rames segar dari dapur Ibu Ida">
        <div class="story-copy">
            <h2>Cerita Kami</h2>
            <p>Berawal dari meja makan keluarga pada 1984, Kantin Ibu Ida tumbuh menjadi tempat merawat cita rasa Indonesia. Bagi Ibu Ida, waktu adalah bumbu paling penting.</p>
            <p>Setiap bumbu diracik telaten, kuah dimasak perlahan, dan sayuran dipilih segar dari pemasok tepercaya. Kami tidak sekadar menyajikan makanan; kami mengantar rasa rumah yang dibuat lebih rapi untuk hari ini.</p>
            <div class="story-stats">
                <div><strong>40+</strong><span>Resep Warisan</span></div>
                <div><strong>3</strong><span>Generasi</span></div>
            </div>
        </div>
    </section>

    <section class="chef-section dashboard-container">
        <div class="section-heading">
            <div>
                <h2>Pilihan Dapur</h2>
                <p>Menu favorit harian yang dipilih langsung dari dapur.</p>
            </div>
            <div class="section-arrows" aria-hidden="true">
                <span><i class="fa-solid fa-chevron-left"></i></span>
                <span><i class="fa-solid fa-chevron-right"></i></span>
            </div>
        </div>

        <div class="chef-scroll">
            @php
                $chefMenus = $menus->isNotEmpty()
                    ? $menus->concat($menus)
                    : collect([
                        (object) ['name' => 'Urap Warisan', 'description' => 'Sayuran kukus tradisional dengan kelapa berbumbu dan sentuhan jeruk limau.', 'price' => 85000, 'image_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=900&auto=format&fit=crop'],
                        (object) ['name' => 'Rendang Padang', 'description' => 'Daging dimasak perlahan dengan santan dan rempah khas Minang.', 'price' => 126000, 'image_url' => 'https://images.unsplash.com/photo-1604909052743-94e838986d24?q=80&w=900&auto=format&fit=crop'],
                        (object) ['name' => 'Bebek Goreng', 'description' => 'Bebek renyah dengan sambal serai segar.', 'price' => 145000, 'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=900&auto=format&fit=crop'],
                    ])->concat(collect([
                        (object) ['name' => 'Urap Warisan', 'description' => 'Sayuran kukus tradisional dengan kelapa berbumbu dan sentuhan jeruk limau.', 'price' => 85000, 'image_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=900&auto=format&fit=crop'],
                        (object) ['name' => 'Rendang Padang', 'description' => 'Daging dimasak perlahan dengan santan dan rempah khas Minang.', 'price' => 126000, 'image_url' => 'https://images.unsplash.com/photo-1604909052743-94e838986d24?q=80&w=900&auto=format&fit=crop'],
                        (object) ['name' => 'Bebek Goreng', 'description' => 'Bebek renyah dengan sambal serai segar.', 'price' => 145000, 'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=900&auto=format&fit=crop'],
                    ]));
            @endphp

            @foreach($chefMenus as $menu)
                <article class="chef-card">
                    <img src="{{ $menu->image_url ?? 'https://images.unsplash.com/photo-1512058564366-18510be2db19?q=80&w=900&auto=format&fit=crop' }}" alt="{{ $menu->name }}">
                    <div class="chef-card-body">
                        <div class="chef-card-title">
                            <h3>{{ $menu->name }}</h3>
                            <span>Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                        </div>
                        <p>{{ $menu->description ?: 'Pilihan nasi rames andalan yang dimasak segar dari dapur Ibu Ida.' }}</p>
                        <a href="{{ route('menu') }}" class="add-order">Tambah Pesanan</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="service-strip dashboard-container">
        <a href="{{ route('menu') }}" class="service-panel">
            <i class="fa-solid fa-bowl-rice"></i>
            <div>
                <h2>Menu Harian Fresh</h2>
                <p>Lauk rumahan dimasak setiap hari, stok tampil langsung di halaman menu.</p>
            </div>
        </a>
        <a href="{{ route('cart') }}" class="service-panel">
            <i class="fa-solid fa-motorcycle"></i>
            <div>
                <h2>Antar Area 2 KM</h2>
                <p>Sistem mengecek jarak alamat sebelum checkout supaya pesanan tetap realistis.</p>
            </div>
        </a>
        <a href="{{ route('cart') }}" class="service-panel">
            <i class="fa-solid fa-qrcode"></i>
            <div>
                <h2>Bayar Digital</h2>
                <p>Checkout terhubung ke Duitku sandbox dengan invoice setelah pesanan dibuat.</p>
            </div>
        </a>
    </section>
</div>
@endsection
