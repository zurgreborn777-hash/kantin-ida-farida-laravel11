@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
<div class="grid grid-cols-3 mb-4">
    <div class="card" style="border-left: 5px solid var(--primary);">
        <h3>Total Pesanan</h3>
        <p style="font-size: 2rem; font-weight: 800; color: var(--primary); margin: 0;">{{ $totalOrders }}</p>
    </div>
    <div class="card" style="border-left: 5px solid var(--secondary);">
        <h3>Pendapatan</h3>
        <p style="font-size: 2rem; font-weight: 800; color: var(--secondary); margin: 0;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
    </div>
    <div class="card" style="border-left: 5px solid var(--accent);">
        <h3>Total Pelanggan</h3>
        <p style="font-size: 2rem; font-weight: 800; color: var(--accent); margin: 0;">{{ $totalUsers }}</p>
    </div>
</div>

<div class="card">
    <h3>Pesanan Terbaru</h3>
    <div class="table-container mt-1">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->user->name }}</td>
                    <td>Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    <td>
                        @if($order->status == 'pending')
                            <span class="badge" style="background:var(--surface); color:var(--text-muted);"><i class="fa-solid fa-cart-shopping"></i> Keranjang</span>
                        @elseif($order->status == 'dibuat')
                            <span class="badge" style="background:var(--accent);"><i class="fa-solid fa-box"></i> Dibuat</span>
                        @elseif($order->status == 'diantar')
                            <span class="badge" style="background:#f39c12; color:white;"><i class="fa-solid fa-motorcycle"></i> Diantar</span>
                        @elseif($order->status == 'sampai')
                            <span class="badge" style="background:#3498db; color:white;"><i class="fa-solid fa-location-dot"></i> Sampai</span>
                        @elseif($order->status == 'selesai')
                            <span class="badge" style="background:#2ecc71; color:white;"><i class="fa-solid fa-check-double"></i> Selesai</span>
                        @else
                            <span class="badge" style="background:var(--surface); color:var(--text-muted);">{{ ucfirst($order->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $order->created_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
