@extends('layouts.admin')

@section('title', 'Ringkasan Dasbor')

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

<div class="card dashboard-orders-card">
    <div class="dashboard-card-head">
        <div>
            <span class="eyebrow">Aktivitas Terbaru</span>
            <h3>Pesanan Terbaru</h3>
        </div>
        <a href="{{ route('admin.orders') }}" class="btn btn-outline">Lihat Semua</a>
    </div>

    <div class="table-container dashboard-table mt-1">
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
                @forelse($orders as $order)
                <tr>
                    <td><strong>#{{ $order->id }}</strong></td>
                    <td>{{ $order->user->name ?? 'User dihapus' }}</td>
                    <td><strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></td>
                    <td>
                        @if($order->status == 'pending')
                            <span class="status-badge status-pending"><i class="fa-solid fa-cart-shopping"></i> Keranjang</span>
                        @elseif($order->status == 'dibuat')
                            <span class="status-badge status-dibuat"><i class="fa-solid fa-box"></i> Dibuat</span>
                        @elseif($order->status == 'diantar')
                            <span class="status-badge status-diantar"><i class="fa-solid fa-motorcycle"></i> Diantar</span>
                        @elseif($order->status == 'sampai')
                            <span class="status-badge status-sampai"><i class="fa-solid fa-location-dot"></i> Sampai</span>
                        @elseif($order->status == 'selesai')
                            <span class="status-badge status-selesai"><i class="fa-solid fa-check-double"></i> Selesai</span>
                        @else
                            <span class="status-badge status-pending">{{ ucfirst($order->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $order->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="dashboard-empty">Belum ada pesanan terbaru.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
