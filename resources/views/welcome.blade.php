@extends('layouts.app')

@section('body_class', 'home-dashboard')

@section('content')
<div class="dashboard-page" x-data="{ kitchenOpen: false }" :class="{ 'kitchen-is-open': kitchenOpen }">
    <button class="kitchen-toggle" type="button" @click="kitchenOpen = true" aria-label="Open kitchen panel">
        <i class="fa-solid fa-bars"></i>
    </button>
    <button class="kitchen-backdrop" type="button" @click="kitchenOpen = false" aria-label="Close kitchen panel"></button>

    <aside class="kitchen-panel" :aria-hidden="!kitchenOpen">
        <div class="kitchen-profile">
            <span><i class="fa-solid fa-user"></i></span>
            <div>
                <strong>Your Kitchen</strong>
                <small>Premium Concierge</small>
            </div>
            <button class="kitchen-close" type="button" @click="kitchenOpen = false" aria-label="Close kitchen panel">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="kitchen-menu" aria-label="Kitchen menu">
            <a class="active" href="{{ route('cart') }}"><i class="fa-solid fa-receipt"></i> Current Order</a>
            <a href="{{ route('orders.my') }}"><i class="fa-solid fa-clock-rotate-left"></i> Past Orders</a>
            <a href="{{ route('menu') }}"><i class="fa-regular fa-heart"></i> Saved Meals</a>
            <a href="{{ route('cart') }}"><i class="fa-regular fa-credit-card"></i> Payment Methods</a>
            <a href="{{ route('profile') }}"><i class="fa-solid fa-gear"></i> Settings</a>
        </nav>

        <a class="kitchen-checkout" href="{{ route('cart') }}">Checkout Now</a>
    </aside>

    <section class="artisan-hero">
        <div class="dashboard-container">
            <span class="artisan-pill">Featured Today</span>
            <h1>The Masterpiece<br><em>Nasi Rames</em></h1>
            <p>An artisanal assembly of heritage recipes, slowly simmered to perfection in Ibu Ida's private kitchen. Comfort, elevated.</p>
            <div class="artisan-actions">
                <a href="{{ route('menu') }}" class="artisan-btn artisan-btn-primary">Book Your Meal</a>
                <a href="{{ route('menu') }}" class="artisan-btn artisan-btn-ghost">View Menu</a>
            </div>
        </div>
    </section>

    <section id="about" class="story-section dashboard-container">
        <img src="https://images.unsplash.com/photo-1551218808-94e220e084d2?q=80&w=1200&auto=format&fit=crop" alt="Fresh nasi rames prepared in a dark kitchen">
        <div class="story-copy">
            <h2>Our Story</h2>
            <p>What began as a small family table in 1984 has evolved into a sanctuary of Indonesian culinary heritage. Ibu Ida's philosophy is simple: time is the most important ingredient.</p>
            <p>Every spice paste is stone-ground, every broth is simmered for twenty-four hours, and every vegetable is sourced from our network of organic highland farmers. We don't just serve food; we deliver memories of home, refined for the modern epicurean.</p>
            <div class="story-stats">
                <div><strong>40+</strong><span>Heritage Recipes</span></div>
                <div><strong>3</strong><span>Generations</span></div>
            </div>
        </div>
    </section>

    <section class="chef-section dashboard-container">
        <div class="section-heading">
            <div>
                <h2>Chef's Picks</h2>
                <p>The seasonal favorites, selected daily.</p>
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
                        (object) ['name' => 'Urap Heritage', 'description' => 'Traditional steamed vegetables with spiced coconut and lime zest.', 'price' => 85000, 'image_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=900&auto=format&fit=crop'],
                        (object) ['name' => 'Rendang Padang', 'description' => 'Slow-braised wagyu beef in coconut milk reduction.', 'price' => 126000, 'image_url' => 'https://images.unsplash.com/photo-1604909052743-94e838986d24?q=80&w=900&auto=format&fit=crop'],
                        (object) ['name' => 'Bebek Goreng', 'description' => 'Crispy half-duck with raw lemongrass sambal.', 'price' => 145000, 'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=900&auto=format&fit=crop'],
                    ])->concat(collect([
                        (object) ['name' => 'Urap Heritage', 'description' => 'Traditional steamed vegetables with spiced coconut and lime zest.', 'price' => 85000, 'image_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=900&auto=format&fit=crop'],
                        (object) ['name' => 'Rendang Padang', 'description' => 'Slow-braised wagyu beef in coconut milk reduction.', 'price' => 126000, 'image_url' => 'https://images.unsplash.com/photo-1604909052743-94e838986d24?q=80&w=900&auto=format&fit=crop'],
                        (object) ['name' => 'Bebek Goreng', 'description' => 'Crispy half-duck with raw lemongrass sambal.', 'price' => 145000, 'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=900&auto=format&fit=crop'],
                    ]));
            @endphp

            @foreach($chefMenus as $menu)
                <article class="chef-card">
                    <img src="{{ $menu->image_url ?? 'https://images.unsplash.com/photo-1512058564366-18510be2db19?q=80&w=900&auto=format&fit=crop' }}" alt="{{ $menu->name }}">
                    <div class="chef-card-body">
                        <div class="chef-card-title">
                            <h3>{{ $menu->name }}</h3>
                            <span>IDR {{ number_format($menu->price / 1000, 0) }}k</span>
                        </div>
                        <p>{{ $menu->description ?: 'Signature nasi rames selection prepared fresh from Ibu Ida kitchen.' }}</p>
                        <a href="{{ route('menu') }}" class="add-order">Add to Order</a>
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
