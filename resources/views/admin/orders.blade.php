@extends('layouts.admin')

@section('title', 'Manajemen Orders')

@section('content')
<div class="card">
    <h3>Semua Pesanan</h3>
    @if(session('success'))
        <div style="color: var(--accent); margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    <div class="table-container mt-1">
        <table>
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Lokasi</th>
                    <th>Detail Pesanan</th>
                    <th>Total Harga</th>
                    <th>Status & Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>#{{ $order->id }}<br><small>{{ $order->created_at->format('d M Y H:i') }}</small></td>
                    <td>{{ $order->user->name }}</td>
                    <td><small style="color: var(--text-muted);">{{ $order->location ?? '-' }}</small></td>
                    <td>
                        <ul style="padding-left:1rem; margin:0; font-size:0.9rem;">
                            @foreach($order->items as $item)
                                <li>{{ $item->quantity }}x {{ $item->menu->name ?? 'Menu Dihapus' }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td style="font-weight:bold;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    <td>
                        <div class="mb-1">
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
                        </div>
                        @if($order->status != 'pending')
                        <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="flex items-center gap-1">
                            @csrf
                            <select name="status" class="input" style="padding:0.2rem 0.5rem; border-radius:var(--radius-md); font-size:0.8rem;">
                                <option value="dibuat" {{ $order->status == 'dibuat' ? 'selected' : '' }}>Dibuat</option>
                                <option value="diantar" {{ $order->status == 'diantar' ? 'selected' : '' }}>Diantar</option>
                                <option value="sampai" {{ $order->status == 'sampai' ? 'selected' : '' }}>Sampai</option>
                                <option value="selesai" {{ $order->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                            <button type="submit" class="action-btn edit" style="padding:0.2rem 0.5rem;"><i class="fa-solid fa-save"></i></button>
                        </form>
                        @endif
                    </td>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
