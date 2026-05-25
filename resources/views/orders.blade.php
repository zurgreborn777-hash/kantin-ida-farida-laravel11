@extends('layouts.app')

@section('body_class', 'home-dashboard')

@section('content')
@php
    $steps = [
        'dibuat' => 'Pesanan Diterima',
        'diantar' => 'Dalam Pengiriman',
        'sampai' => 'Sampai',
        'selesai' => 'Selesai',
    ];
    $icons = [
        'dibuat' => 'fa-circle-check',
        'diantar' => 'fa-motorcycle',
        'sampai' => 'fa-house',
        'selesai' => 'fa-check-double',
    ];
    $rank = ['dibuat' => 1, 'diantar' => 2, 'sampai' => 3, 'selesai' => 4];
    $statusCopy = [
        'dibuat' => 'Sedang Diproses',
        'diantar' => 'Sedang Dalam Pengiriman',
        'sampai' => 'Menunggu Konfirmasi',
        'selesai' => 'Pesanan Selesai',
    ];
@endphp

<section class="orders-page">
    <div class="dashboard-container">
        <div class="orders-hero animate-fade-in-up">
            <span>Lacak Pesanan</span>
            <h1>Pesanan Saya</h1>
            <p>Pantau status dapur, pengiriman, dan ringkasan pembayaran dari setiap pesananmu.</p>
        </div>

        @if(session('success'))
            <div class="orders-alert">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="orders-alert orders-alert-error">{{ $errors->first() }}</div>
        @endif

        <div class="orders-list">
            @forelse($orders as $order)
            @php
                $currentRank = $rank[$order->status] ?? 0;
                $subtotal = $order->items->sum(fn ($item) => $item->price * $item->quantity);
                $shippingFee = $order->shipping_fee ?? max(0, $order->total_price - $subtotal);
                $itemCount = $order->items->sum('quantity');
            @endphp

            <article class="orders-tracker animate-fade-in-up" x-data="{ open: false }" :class="{ 'is-open': open }">
                <button type="button" class="orders-title-card orders-accordion-trigger" @click="open = !open" :aria-expanded="open.toString()">
                    <div>
                        <h2>Pesanan Saya</h2>
                        <p>ID Pesanan: #ID-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }} &bull; {{ $order->created_at->format('d F Y') }}</p>
                    </div>
                    <div class="orders-title-actions">
                        <span>{{ $statusCopy[$order->status] ?? ucfirst($order->status) }}</span>
                        <small>{{ $itemCount }} item &bull; Rp {{ number_format($order->total_price, 0, ',', '.') }}</small>
                        <i class="fa-solid fa-chevron-down" :class="{ 'rotate': open }"></i>
                    </div>
                </button>

                <div class="orders-accordion-panel" x-show="open" style="display: none;">
                    <div class="orders-main">
                        <div class="orders-progress-card">
                            <div class="orders-progress-line" style="--progress: {{ max(0, min(100, (($currentRank - 1) / 3) * 100)) }}%;"></div>
                            <div class="orders-step-grid">
                                @foreach($steps as $key => $label)
                                <div class="orders-step {{ $currentRank >= $rank[$key] ? 'active' : '' }}">
                                    <div><i class="fa-solid {{ $icons[$key] }}"></i></div>
                                    <span>{{ $label }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="orders-info-grid">
                            <div class="orders-map-card">
                                <iframe
                                    src="https://maps.google.com/maps?q={{ urlencode($order->location ?: 'Kantin Ibu Ida') }}&output=embed"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="Peta pengiriman pesanan #{{ $order->id }}"
                                ></iframe>
                                <div class="orders-address-card">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <div>
                                        <strong>Alamat Pengiriman</strong>
                                        <span>{{ $order->location ?? 'Alamat belum tersedia' }}</span>
                                        @if($order->distance_km !== null)
                                            <small>{{ number_format($order->distance_km, 2, ',', '.') }} KM dari kantin</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="orders-eta-card">
                                <span>Estimasi Kedatangan</span>
                                <h3>{{ $order->status === 'selesai' ? 'Selesai' : ($order->status === 'sampai' ? 'Tiba' : '15 Menit') }}</h3>
                                <p>
                                    @if($order->status === 'dibuat')
                                        Dapur Ibu Ida sedang menyiapkan pesananmu dengan urutan terbaik.
                                    @elseif($order->status === 'diantar')
                                        Kurir kami sedang dalam perjalanan menuju lokasimu melalui rute tercepat.
                                    @elseif($order->status === 'sampai')
                                        Pesanan sudah sampai. Konfirmasi jika hidangan sudah kamu terima.
                                    @else
                                        Terima kasih, pesanan ini sudah selesai diproses.
                                    @endif
                                </p>

                                <div class="orders-action-row">
                                    <a href="{{ route('invoice.show', $order->id) }}">
                                        <i class="fa-solid fa-file-invoice"></i>
                                        <span>Invoice</span>
                                    </a>

                                    @if($order->status == 'sampai')
                                    <form action="{{ route('orders.confirm', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit">
                                            <i class="fa-solid fa-check-circle"></i>
                                            <span>Konfirmasi</span>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside class="orders-summary-card">
                        <h2>Ringkasan Pesanan</h2>

                        <div class="orders-summary-items">
                            @foreach($order->items as $item)
                            <div class="orders-summary-item">
                                <img src="{{ $item->menu->image_url ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=200&auto=format&fit=crop' }}" alt="{{ $item->menu->name ?? 'Menu Dihapus' }}">
                                <div>
                                    <strong>{{ $item->menu->name ?? 'Menu Dihapus' }}</strong>
                                    <span>{{ $item->quantity }}x &bull; {{ $item->menu->category ?? 'Menu Harian' }}</span>
                                </div>
                                <b>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</b>
                            </div>
                            @endforeach
                        </div>

                        <div class="orders-price-stack">
                            <div>
                                <span>Subtotal</span>
                                <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span>Ongkos Kirim</span>
                                <strong>Rp {{ number_format($shippingFee, 0, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span>Jumlah Item</span>
                                <strong>{{ $itemCount }}</strong>
                            </div>
                        </div>

                        <div class="orders-total-box">
                            <span>Total Pembayaran</span>
                            <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                            <small>{{ strtoupper($order->payment_status ?? 'paid') }} via {{ strtoupper($order->payment_method ?? 'Duitku') }}</small>
                        </div>
                    </aside>
                </div>
            </article>
            @empty
            <div class="orders-empty animate-fade-in-up">
                <i class="fa-solid fa-box-open"></i>
                <h3>Belum ada pesanan</h3>
                <p>Anda belum pernah melakukan pesanan.</p>
                <a href="{{ route('menu') }}">Pesan Sekarang</a>
            </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
