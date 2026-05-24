@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'Invoice Pesanan')

@section('content')
@php
    $subtotal = $order->items->sum(fn ($item) => $item->price * $item->quantity);
    $paymentStatus = $order->payment_status ?: ($order->status === 'pending' ? 'pending' : 'paid');
    $paymentMethod = $order->payment_method ?: '-';
@endphp

<section class="{{ auth()->user()->is_admin ? '' : 'p-2 mb-4' }}">
    <div class="{{ auth()->user()->is_admin ? '' : 'container' }}">
        <div class="invoice-actions no-print">
            <a href="{{ auth()->user()->is_admin ? route('admin.orders') : route('orders.my') }}" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print / Simpan PDF
            </button>
        </div>

        <div class="invoice-sheet">
            <div class="invoice-header">
                <div>
                    <p class="invoice-kicker">Invoice / Bukti Pembayaran</p>
                    <h2>Kantin Ibu Ida</h2>
                    <p>{{ config('app.url') }}</p>
                </div>
                <div class="invoice-number">
                    <span>ID Pesanan</span>
                    <strong>#{{ $order->id }}</strong>
                </div>
            </div>

            <div class="invoice-meta">
                <div>
                    <span>Tanggal & Jam</span>
                    <strong>{{ $order->created_at->format('d M Y H:i') }}</strong>
                </div>
                <div>
                    <span>Nama Pelanggan</span>
                    <strong>{{ $order->user->name ?? '-' }}</strong>
                </div>
                <div>
                    <span>Status Pesanan</span>
                    <strong>{{ ucfirst($order->status) }}</strong>
                </div>
                <div>
                    <span>Status Pembayaran</span>
                    <strong>{{ ucfirst($paymentStatus) }}</strong>
                </div>
                <div>
                    <span>Metode Pembayaran</span>
                    <strong>{{ $paymentMethod }}</strong>
                </div>
                <div>
                    <span>Alamat</span>
                    <strong>{{ $order->location ?? '-' }}</strong>
                </div>
            </div>

            <div class="invoice-table-wrap">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Item/Menu</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->menu->name ?? 'Menu Dihapus' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="invoice-total">
                <div>
                    <span>Subtotal</span>
                    <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Ongkir</span>
                    <strong>Rp {{ number_format($order->shipping_fee ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="grand-total">
                    <span>Total Pembayaran</span>
                    <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
