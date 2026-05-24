@extends('layouts.app')

@section('content')
<section class="home-hero">
    <div class="home-marquee" aria-hidden="true">
        <div>
            <span>BUKA HARI INI</span><span>NASI RAMES HANGAT</span><span>ANTAR 2 KM</span><span>DUITKU SANDBOX</span>
            <span>BUKA HARI INI</span><span>NASI RAMES HANGAT</span><span>ANTAR 2 KM</span><span>DUITKU SANDBOX</span>
        </div>
    </div>

    <div class="container home-hero-shell">
        <div class="home-hero-copy animate-fade-in-up">
            <span class="home-chip"><i class="fa-solid fa-bowl-rice"></i> Kantin Ibu Ida</span>
            <h1>Nasi rames rumahan yang siap bikin jam makan lebih tenang.</h1>
            <p>
                Pilih menu favorit, tentukan lokasi, lalu bayar aman lewat Duitku sandbox.
                Pesanan diantar untuk area maksimal 2 KM dari kantin.
            </p>

            <div class="home-hero-actions">
                <a href="{{ route('menu') }}" class="btn btn-primary">
                    <i class="fa-solid fa-utensils"></i> Lihat Menu & Pesan
                </a>
                <a href="#about" class="btn btn-outline">Tentang Kami</a>
            </div>

            <div class="home-stats">
                <div>
                    <strong>2 KM</strong>
                    <span>jangkauan antar</span>
                </div>
                <div>
                    <strong>Fresh</strong>
                    <span>dimasak harian</span>
                </div>
                <div>
                    <strong>QRIS</strong>
                    <span>pembayaran sandbox</span>
                </div>
            </div>
        </div>

        <div class="home-food-stage animate-fade-in-up delay-200">
            <span class="home-doodle doodle-rice"><i class="fa-solid fa-bowl-food"></i></span>
            <span class="home-doodle doodle-spoon"><i class="fa-solid fa-spoon"></i></span>
            <span class="home-doodle doodle-pepper"><i class="fa-solid fa-pepper-hot"></i></span>
            <div class="home-food-card">
                <img src="https://images.unsplash.com/photo-1596797038530-2c107229654b?q=80&w=1100&auto=format&fit=crop" alt="Nasi rames Kantin Ibu Ida">
                <div class="home-food-label">
                    <span>Menu Favorit</span>
                    <strong>Nasi Rames Spesial</strong>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="about" class="home-about">
    <div class="container home-about-grid">
        <div class="home-about-card animate-fade-in-up">
            <span class="eyebrow">Tentang Kantin</span>
            <h2>Masakan sederhana, rasa akrab, dibuat untuk makan harian.</h2>
            <p>
                Kantin Ibu Ida menyajikan menu rumahan yang higienis, terjangkau,
                dan praktis dipesan dari website. Cocok untuk makan siang, pesanan cepat,
                atau kebutuhan kasir kantin.
            </p>
        </div>
        <div class="home-about-list animate-fade-in-up delay-100">
            <div>
                <i class="fa-solid fa-location-dot"></i>
                <span>Validasi lokasi maksimal 2 KM.</span>
            </div>
            <div>
                <i class="fa-solid fa-receipt"></i>
                <span>Invoice dan bukti pembayaran tersedia.</span>
            </div>
            <div>
                <i class="fa-solid fa-motorcycle"></i>
                <span>Status pengiriman mudah dipantau.</span>
            </div>
        </div>
    </div>
</section>
@endsection
