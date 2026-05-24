@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'Invoice Pesanan')

@section('content')
@php
    $subtotal = $order->items->sum(fn ($item) => $item->price * $item->quantity);
    $shippingFee = $order->shipping_fee ?? 0;
    $paymentStatus = $order->payment_status ?: ($order->status === 'pending' ? 'pending' : 'paid');
    $paymentMethod = $order->payment_method ?: '-';
    $invoiceNumber = $order->merchant_order_id ?: str_pad((string) $order->id, 12, '0', STR_PAD_LEFT);
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

        <div class="invoice-sheet compact-invoice">
            <div class="invoice-watermark">
                <span>Rp</span>
                <strong>{{ strtolower($paymentStatus) === 'paid' ? 'LUNAS' : strtoupper($paymentStatus) }}</strong>
            </div>

            <header class="invoice-header">
                <div class="invoice-brand">
                    <i class="fa-solid fa-bowl-rice"></i>
                    <span>Kantin Ibu Ida</span>
                </div>
                <div class="invoice-number">
                    <strong>INVOICE</strong>
                    <span>{{ $invoiceNumber }}</span>
                </div>
            </header>

            <section class="invoice-parties">
                <div class="invoice-party">
                    <h3>Diterbitkan Atas Nama</h3>
                    <div class="invoice-party-row">
                        <span>Penjual</span>
                        <strong>Kantin Ibu Ida</strong>
                    </div>
                </div>

                <div class="invoice-party">
                    <h3>Untuk</h3>
                    <div class="invoice-party-row">
                        <span>Pembeli</span>
                        <strong>{{ $order->user->name ?? '-' }}</strong>
                    </div>
                    <div class="invoice-party-row">
                        <span>Tanggal Pembelian</span>
                        <strong>{{ $order->created_at->format('d M Y') }}</strong>
                    </div>
                    <div class="invoice-party-row align-start">
                        <span>Alamat Pengiriman</span>
                        <strong>{{ $order->location ?? '-' }}</strong>
                    </div>
                </div>
            </section>

            <div class="invoice-table-wrap">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Info Produk</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <strong class="invoice-product-name">{{ $item->menu->name ?? 'Menu Dihapus' }}</strong>
                                <small>Status pesanan: {{ ucfirst($order->status) }}</small>
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <section class="invoice-summary">
                <div class="invoice-badge">PLUS</div>

                <div class="invoice-total">
                    <div>
                        <span>Subtotal Harga Barang</span>
                        <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span>Total Ongkos Kirim</span>
                        <strong>Rp {{ number_format($shippingFee, 0, ',', '.') }}</strong>
                    </div>
                    <div class="grand-total">
                        <span>Total Tagihan</span>
                        <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                    </div>
                    <div class="invoice-payment-note">
                        <span>Metode Pembayaran:</span>
                        <strong>{{ $paymentMethod }}</strong>
                    </div>
                </div>
            </section>

            <footer class="invoice-footer">
                <span>Invoice ini sah dan diproses oleh sistem Kantin Ibu Ida.</span>
                <span>Dicetak: {{ now()->format('d M Y H:i') }}</span>
            </footer>
        </div>
    </div>
</section>
@endsection
