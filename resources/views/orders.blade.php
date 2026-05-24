@extends('layouts.app')

@section('content')
@php
    $steps = ['dibuat' => 'Dibuat', 'diantar' => 'Diantar', 'sampai' => 'Sampai', 'selesai' => 'Selesai'];
    $icons = ['dibuat' => 'fa-box', 'diantar' => 'fa-motorcycle', 'sampai' => 'fa-location-dot', 'selesai' => 'fa-circle-check'];
    $rank = ['dibuat' => 1, 'diantar' => 2, 'sampai' => 3, 'selesai' => 4];
@endphp

<section class="hero compact-hero">
    <div class="container text-center animate-fade-in-up">
        <h2>Pesanan Saya</h2>
        <p>Pantau pengiriman, detail pesanan, dan bukti pembayaran Anda.</p>
    </div>
</section>

<section class="p-2 mb-4">
    <div class="container">
        @if(session('success'))
            <div class="notice notice-success mb-2">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="notice notice-danger mb-2">{{ $errors->first() }}</div>
        @endif

        <div class="accordion-list">
            @forelse($orders as $order)
            @php
                $currentRank = $rank[$order->status] ?? 0;
                $subtotal = $order->items->sum(fn ($item) => $item->price * $item->quantity);
                $itemCount = $order->items->sum('quantity');
            @endphp
            <article class="accordion-card animate-fade-in-up" x-data="{ open: false }" :class="{ 'is-open': open }">
                <button type="button" class="accordion-trigger" @click="open = !open" :aria-expanded="open.toString()">
                    <div class="accordion-main">
                        <span class="eyebrow">Pesanan #{{ $order->id }}</span>
                        <strong>{{ $order->created_at->format('d M Y H:i') }}</strong>
                    </div>
                    <div class="accordion-summary">
                        <span>{{ $itemCount }} item</span>
                        <b>Rp {{ number_format($order->total_price, 0, ',', '.') }}</b>
                        <span class="status-badge status-{{ $order->status }}">
                            <i class="fa-solid {{ $icons[$order->status] ?? 'fa-receipt' }}"></i>
                            {{ ucfirst($order->status) }}
                        </span>
                        <i class="fa-solid fa-chevron-down accordion-chevron" :class="{ 'rotate': open }"></i>
                    </div>
                </button>

                <div class="accordion-panel" x-show="open" x-transition:enter="accordion-enter" x-transition:enter-start="accordion-enter-start" x-transition:enter-end="accordion-enter-end" x-transition:leave="accordion-leave" x-transition:leave-start="accordion-leave-start" x-transition:leave-end="accordion-leave-end">
                    <div class="accordion-panel-inner">
                        <div class="status-steps compact-status-steps">
                            @foreach($steps as $key => $label)
                            <div class="status-step {{ $currentRank >= $rank[$key] ? 'active' : '' }}">
                                <div class="status-dot"><i class="fa-solid {{ $icons[$key] }}"></i></div>
                                <span>{{ $label }}</span>
                            </div>
                            @endforeach
                        </div>

                        <div class="order-detail-grid">
                            <div>
                                <h4>Alamat Pengiriman</h4>
                                <p>{{ $order->location ?? '-' }}</p>
                                @if($order->distance_km !== null)
                                    <small>{{ number_format($order->distance_km, 2, ',', '.') }} KM dari kantin</small>
                                @endif
                            </div>
                            <div>
                                <h4>Pembayaran</h4>
                                <p>{{ ucfirst($order->payment_status ?? 'paid') }} - {{ $order->payment_method ?? '-' }}</p>
                                <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="order-items">
                            @foreach($order->items as $item)
                            <div class="order-item-row">
                                <div>
                                    <strong>{{ $item->menu->name ?? 'Menu Dihapus' }}</strong>
                                    <span>{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                </div>
                                <b>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</b>
                            </div>
                            @endforeach
                            <div class="order-item-row muted">
                                <div>Subtotal</div>
                                <b>Rp {{ number_format($subtotal, 0, ',', '.') }}</b>
                            </div>
                            <div class="order-item-row muted">
                                <div>Ongkir</div>
                                <b>Rp {{ number_format($order->shipping_fee ?? 0, 0, ',', '.') }}</b>
                            </div>
                        </div>

                        <div class="order-actions">
                            <a href="{{ route('invoice.show', $order->id) }}" class="btn btn-outline">
                                <i class="fa-solid fa-file-invoice"></i> Lihat Invoice / Bukti Pembayaran
                            </a>
                            @if($order->status == 'sampai')
                            <form action="{{ route('orders.confirm', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-check-circle"></i> Konfirmasi Pesanan Diterima
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
            @empty
            <div class="card text-center animate-fade-in-up" style="padding: 4rem 2rem;">
                <i class="fa-solid fa-box-open" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <h3>Belum ada pesanan</h3>
                <p>Anda belum pernah melakukan pesanan.</p>
                <a href="{{ route('menu') }}" class="btn btn-primary mt-2">Pesan Sekarang</a>
            </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
