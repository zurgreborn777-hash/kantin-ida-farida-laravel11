@extends('layouts.admin')

@section('title', 'Kasir (Point of Sale)')

@section('content')
<div class="grid" style="grid-template-columns: 2fr 1fr; gap: 1rem; align-items: start;" x-data="kasirApp()">
    
    <!-- Menu List -->
    <div class="card">
        <h3>Pilih Menu</h3>
        <div class="grid grid-cols-3 gap-1 mt-2">
            @foreach($menus as $menu)
            <div class="card" style="padding: 0.5rem; cursor: pointer; display: flex; flex-direction: column;" @click="addToCart({{ $menu->id }}, '{{ addslashes($menu->name) }}', {{ $menu->price }}, {{ $menu->stock }})">
                <img src="{{ $menu->image_url ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=200' }}" style="width: 100%; height: 100px; object-fit: cover; border-radius: var(--radius-md); margin-bottom: 0.5rem;">
                <div style="font-weight: bold; font-size: 0.9rem;">{{ $menu->name }}</div>
                <div style="color: var(--primary); font-size: 0.8rem; font-weight: bold;">Rp {{ number_format($menu->price, 0, ',', '.') }}</div>
                <div style="color: var(--text-muted); font-size: 0.7rem; margin-top: auto;">Sisa: {{ $menu->stock }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Cart / Order Summary -->
    <div class="card" style="position: sticky; top: 1rem;">
        <h3>Pesanan Kasir</h3>
        
        <div class="form-group mt-1">
            <label class="label">Nama Pelanggan *</label>
            <input type="text" x-model="customerName" class="input" placeholder="Nama Pembeli" required>
        </div>

        <div style="max-height: 300px; overflow-y: auto; border-top: 1px solid var(--theme-border); border-bottom: 1px solid var(--theme-border); padding: 0.5rem 0; margin-bottom: 1rem;">
            <template x-if="cart.length === 0">
                <div class="text-center" style="color: var(--text-muted); padding: 1rem 0;">Belum ada menu dipilih.</div>
            </template>
            <template x-for="(item, index) in cart" :key="index">
                <div class="flex justify-between items-center mb-1" style="font-size: 0.9rem;">
                    <div>
                        <div style="font-weight: bold;" x-text="item.name"></div>
                        <div style="color: var(--primary);">Rp <span x-text="formatPrice(item.price)"></span></div>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="decreaseQty(index)" class="btn btn-outline" style="padding: 0.1rem 0.4rem;">-</button>
                        <span x-text="item.quantity" style="width: 20px; text-align: center; font-weight: bold;"></span>
                        <button @click="increaseQty(index)" class="btn btn-outline" style="padding: 0.1rem 0.4rem;">+</button>
                        <button @click="removeItem(index)" class="action-btn delete" style="padding: 0.2rem 0.4rem; margin-left: 0.5rem;"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex justify-between mb-1">
            <span style="font-weight: bold;">Total</span>
            <span style="font-weight: bold; color: var(--primary);">Rp <span x-text="formatPrice(totalPrice)"></span></span>
        </div>

        <div class="mb-2">
            <label class="label">Metode Pembayaran</label>
            <select x-model="paymentMethod" class="input">
                @forelse($paymentMethods as $pm)
                    <option value="{{ $pm['paymentMethod'] }}">{{ $pm['paymentName'] }}</option>
                @empty
                    <option value="">Tidak ada metode pembayaran tersedia</option>
                @endforelse
            </select>
        </div>

        <button @click="checkout()" class="btn btn-primary" style="width: 100%;" :disabled="isProcessing || cart.length === 0">
            <span x-show="!isProcessing"><i class="fa-solid fa-cash-register"></i> Bayar & Proses</span>
            <span x-show="isProcessing"><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span>
        </button>
    </div>

</div>

<script>
function kasirApp() {
    return {
        cart: [],
        customerName: '',
        paymentMethod: '{{ count($paymentMethods) > 0 ? $paymentMethods[0]['paymentMethod'] : '' }}',
        isProcessing: false,

        get totalPrice() {
            return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        },

        formatPrice(price) {
            return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        },

        addToCart(id, name, price, maxStock) {
            const existing = this.cart.find(item => item.id === id);
            if (existing) {
                if (existing.quantity < maxStock) {
                    existing.quantity++;
                } else {
                    alert('Stok maksimal tercapai untuk ' + name);
                }
            } else {
                if (maxStock > 0) {
                    this.cart.push({ id, name, price, quantity: 1, maxStock });
                }
            }
        },

        increaseQty(index) {
            if (this.cart[index].quantity < this.cart[index].maxStock) {
                this.cart[index].quantity++;
            } else {
                alert('Stok maksimal tercapai!');
            }
        },

        decreaseQty(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity--;
            } else {
                this.removeItem(index);
            }
        },

        removeItem(index) {
            this.cart.splice(index, 1);
        },

        async checkout() {
            if (this.cart.length === 0) return alert('Keranjang kosong!');
            if (!this.customerName.trim()) return alert('Masukkan nama pelanggan!');
            
            this.isProcessing = true;
            try {
                const response = await fetch('{{ route('admin.kasir.checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        items: this.cart,
                        customerName: this.customerName,
                        paymentMethod: this.paymentMethod
                    })
                });
                
                const data = await response.json();
                
                if (data.success && data.payment_url) {
                    // Redirect to payment url
                    window.location.href = data.payment_url;
                } else {
                    alert(data.message || 'Gagal checkout Kasir');
                    this.isProcessing = false;
                }
            } catch(e) {
                alert('Gagal terhubung ke server');
                this.isProcessing = false;
            }
        }
    }
}
</script>
@endsection
