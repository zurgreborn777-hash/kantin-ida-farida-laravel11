@extends('layouts.admin')

@section('title', 'Manajemen Orders')

@section('content')
@php
    $steps = ['dibuat' => 'Dibuat', 'diantar' => 'Diantar', 'sampai' => 'Sampai', 'selesai' => 'Selesai'];
    $icons = ['pending' => 'fa-cart-shopping', 'dibuat' => 'fa-box', 'diantar' => 'fa-motorcycle', 'sampai' => 'fa-location-dot', 'selesai' => 'fa-circle-check'];
    $rank = ['dibuat' => 1, 'diantar' => 2, 'sampai' => 3, 'selesai' => 4];
@endphp

<div class="admin-page-head">
    <div>
        <p class="eyebrow">Order Management</p>
        <h3>Daftar Pesanan</h3>
    </div>
    <span class="admin-count">{{ $orders->count() }} pesanan</span>
</div>

@if(session('success'))
    <div class="notice notice-success mb-2">{{ session('success') }}</div>
@endif

<div class="admin-order-list">
    @foreach($orders as $order)
    @php
        $currentRank = $rank[$order->status] ?? 0;
        $subtotal = $order->items->sum(fn ($item) => $item->price * $item->quantity);
    @endphp
    <article class="admin-order-card">
        <div class="admin-order-main">
            <div class="admin-order-title">
                <div>
                    <span class="eyebrow">#{{ $order->id }} · {{ $order->created_at->format('d M Y H:i') }}</span>
                    <h4>{{ $order->user->name ?? 'User dihapus' }}</h4>
                </div>
                <span class="status-badge status-{{ $order->status }}">
                    <i class="fa-solid {{ $icons[$order->status] ?? 'fa-receipt' }}"></i>
                    {{ $order->status == 'pending' ? 'Keranjang' : ucfirst($order->status) }}
                </span>
            </div>

            <div class="admin-order-meta">
                <div>
                    <span>Alamat</span>
                    <strong>{{ $order->location ?? '-' }}</strong>
                </div>
                <div>
                    <span>Pembayaran</span>
                    <strong>{{ ucfirst($order->payment_status ?? 'pending') }} · {{ $order->payment_method ?? '-' }}</strong>
                </div>
                <div>
                    <span>Total</span>
                    <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                </div>
            </div>

            @if($order->status != 'pending')
            <div class="status-steps admin-status-steps">
                @foreach($steps as $key => $label)
                <div class="status-step {{ $currentRank >= $rank[$key] ? 'active' : '' }}">
                    <div class="status-dot"><i class="fa-solid {{ $icons[$key] }}"></i></div>
                    <span>{{ $label }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <div class="admin-items">
                @foreach($order->items as $item)
                <div>
                    <span>{{ $item->quantity }}x {{ $item->menu->name ?? 'Menu Dihapus' }}</span>
                    <strong>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</strong>
                </div>
                @endforeach
                <div>
                    <span>Subtotal</span>
                    <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Ongkir</span>
                    <strong>Rp {{ number_format($order->shipping_fee ?? 0, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="admin-order-actions">
            <a href="{{ route('invoice.show', $order->id) }}" class="btn btn-outline">
                <i class="fa-solid fa-file-invoice"></i> Invoice
            </a>
            @if($order->status != 'pending')
            <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                @csrf
                <label class="label">Ubah Status</label>
                <select name="status" class="input">
                    <option value="dibuat" {{ $order->status == 'dibuat' ? 'selected' : '' }}>Dibuat</option>
                    <option value="diantar" {{ $order->status == 'diantar' ? 'selected' : '' }}>Diantar</option>
                    <option value="sampai" {{ $order->status == 'sampai' ? 'selected' : '' }}>Sampai</option>
                    <option value="selesai" {{ $order->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:0.75rem;">
                    <i class="fa-solid fa-save"></i> Simpan
                </button>
            </form>
            @endif
        </div>
    </article>
    @endforeach
</div>
@endsection
