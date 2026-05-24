@extends('layouts.app')

@section('content')
<section class="hero" style="min-height: auto; padding: 6rem 0 3rem 0; background: var(--surface);">
    <div class="container text-center animate-fade-in-up">
        <h2>Pesanan Saya</h2>
        <p>Pantau status pesanan Anda dan konfirmasi penerimaan.</p>
    </div>
</section>

<section class="p-2 mb-4">
    <div class="container">
        @if(session('success'))
            <div class="card mb-2" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71; border-color: #2ecc71;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="card mb-2" style="background: rgba(255, 51, 102, 0.1); color: var(--primary); border-color: var(--primary);">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid" style="grid-template-columns: 1fr; gap: 1rem;">
            @forelse($orders as $order)
            <div class="card animate-fade-in-up">
                <div class="flex justify-between items-center" style="border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 1rem; margin-bottom: 1rem;">
                    <div>
                        <h4 style="margin:0;">Order #{{ $order->id }}</h4>
                        <small style="color: var(--text-muted);">{{ $order->created_at->format('d M Y H:i') }}</small>
                    </div>
                    <div>
                        @if($order->status == 'dibuat')
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
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <p style="margin:0; font-weight:bold;">Lokasi Pengiriman:</p>
                        <p style="margin:0; font-size:0.9rem; color:var(--text-muted);">{{ $order->location ?? '-' }}</p>
                        
                        <p style="margin-top:0.5rem; font-weight:bold;">Total Pembayaran:</p>
                        <p style="margin:0; font-size:1.1rem; color:var(--primary); font-weight:900;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p style="margin:0; font-weight:bold;">Item Pesanan:</p>
                        <ul style="padding-left:1rem; margin:0; font-size:0.9rem;">
                            @foreach($order->items as $item)
                                <li>{{ $item->quantity }}x {{ $item->menu->name ?? 'Menu Dihapus' }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                @if($order->status == 'sampai')
                <div style="margin-top: 1rem; text-align: right; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 1rem;">
                    <form action="{{ route('orders.confirm', $order->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check-circle"></i> Konfirmasi Pesanan Diterima</button>
                    </form>
                    <small style="display:block; margin-top:0.5rem; color:var(--text-muted);">*Pesanan akan otomatis selesai dalam 1x24 jam.</small>
                </div>
                @endif
            </div>
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
