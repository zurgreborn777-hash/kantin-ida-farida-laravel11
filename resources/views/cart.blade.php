@extends('layouts.app')

@section('content')
<!-- Leaflet Map CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Duitku Payment Integration -->

<section class="hero" style="min-height: auto; padding: 6rem 0 3rem 0; background: var(--surface);">
    <div class="container text-center animate-fade-in-up">
        <h2>Keranjang Belanja</h2>
        <p>Review pesanan Anda sebelum melakukan pembayaran.</p>
    </div>
</section>

<section class="p-2 mb-4">
    <div class="container">
        
        @if(session('success'))
            <div class="card mb-2" style="background: rgba(0, 210, 211, 0.1); color: var(--accent); border-color: var(--accent);">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="card mb-2" style="background: rgba(255, 51, 102, 0.1); color: var(--primary); border-color: var(--primary);">
                {{ $errors->first() }}
            </div>
        @endif

        @if($order && $order->items->count() > 0)
        <div class="grid" style="grid-template-columns: 2fr 1fr;" x-data="{ 
            totalPrice: '{{ number_format($order->total_price, 0, ',', '.') }}',
            basePrice: {{ (int)$order->total_price }},
            paymentMethod: 'SP',
            location: '',
            
            // Map & Shipping simulation state
            canteenCoords: [{{ config('canteen.latitude') }}, {{ config('canteen.longitude') }}],
            maxDeliveryKm: {{ config('canteen.max_delivery_km') }},
            buyerCoords: null,
            distance: 0,
            ongkir: 0,
            locationInRange: true,
            distanceMessage: 'Pilih lokasi pengiriman.',
            
            initMap() {
                // Initialize map centered at canteen coordinates
                const map = L.map('map').setView(this.canteenCoords, 14);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                // Add Red Marker for Canteen
                L.marker(this.canteenCoords, {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map).bindPopup('<b>Kantin Ibu Ida</b>').openPopup();

                // Add Draggable Blue Marker for Buyer
                const defaultBuyer = [this.canteenCoords[0] - 0.005, this.canteenCoords[1] + 0.005];
                this.buyerCoords = defaultBuyer;
                
                const buyerMarker = L.marker(defaultBuyer, {
                    draggable: true,
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map);

                let routeLine = null;

                // Function to fetch route from OSRM and reverse geocode using Nominatim
                const getRouteAndAddress = async (lat, lng) => {
                    try {
                        // 1. Fetch Driving Route and Distance from OSRM API
                        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${this.canteenCoords[1]},${this.canteenCoords[0]};${lng},${lat}?overview=full&geometries=geojson`;
                        const response = await fetch(osrmUrl);
                        const data = await response.json();
                        
                        if (data.routes && data.routes.length > 0) {
                            const distanceMeters = data.routes[0].distance;
                            this.distance = (distanceMeters / 1000).toFixed(2);
                            
                            // Shipping Fee: Rp 5.000 / 500 meters (0.5 Km)
                            this.ongkir = Math.ceil(this.distance * 2) * 5000;
                            this.locationInRange = parseFloat(this.distance) <= this.maxDeliveryKm;
                            this.distanceMessage = this.locationInRange
                                ? `Lokasi masuk jangkauan ${this.maxDeliveryKm} KM`
                                : 'Lokasi di luar jangkauan 2 KM';
                            
                            // Draw Route Polyline
                            if (routeLine) map.removeLayer(routeLine);
                            routeLine = L.geoJSON(data.routes[0].geometry, {
                                style: { color: '#FF3366', weight: 5, opacity: 0.7 }
                            }).addTo(map);

                            // Fit bounds to show both markers
                            const bounds = L.latLngBounds([this.canteenCoords, [lat, lng]]);
                            map.fitBounds(bounds, { padding: [40, 40] });
                        }

                        // 2. Reverse Geocode coordinate using OpenStreetMap Nominatim
                        const geocodeUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`;
                        const geoResponse = await fetch(geocodeUrl);
                        const geoData = await geoResponse.json();
                        if (geoData && geoData.display_name) {
                            this.location = geoData.display_name;
                        } else {
                            this.location = `Koordinat: ${lat}, ${lng}`;
                        }
                    } catch (err) {
                        console.error('Routing / Geocoding Error:', err);
                    }
                };

                // Initial calculation
                getRouteAndAddress(defaultBuyer[0], defaultBuyer[1]);

                // Update on marker drag
                buyerMarker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.buyerCoords = [pos.lat, pos.lng];
                    getRouteAndAddress(pos.lat, pos.lng);
                });

                // Update on map click
                map.on('click', (e) => {
                    buyerMarker.setLatLng(e.latlng);
                    this.buyerCoords = [e.latlng.lat, e.latlng.lng];
                    getRouteAndAddress(e.latlng.lat, e.latlng.lng);
                });
            },
            
            async updateQuantity(itemId, action) {
                const response = await fetch(`/cart/update/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ action: action })
                });
                const data = await response.json();
                if (data.success) {
                    $store.cart.updateCount(data.cartCount);
                    this.totalPrice = data.totalPrice;
                    this.basePrice = parseInt(data.totalPrice.replace(/\./g, ''));
                    if (data.itemQuantity === 0) {
                        document.getElementById(`cart-item-${itemId}`).remove();
                        if (data.cartCount === 0) location.reload();
                    } else {
                        document.getElementById(`item-qty-${itemId}`).innerText = data.itemQuantity;
                    }
                } else {
                    alert(data.message || 'Gagal mengubah jumlah');
                }
            },
            async removeItem(itemId) {
                if (!confirm('Hapus item ini?')) return;
                const response = await fetch(`/cart/remove/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    $store.cart.updateCount(data.cartCount);
                    this.totalPrice = data.totalPrice;
                    this.basePrice = parseInt(data.totalPrice.replace(/\./g, ''));
                    document.getElementById(`cart-item-${itemId}`).remove();
                    if (data.cartCount === 0) location.reload();
                }
            },
            isProcessing: false,
            async checkout(paymentMethod) {
                if (!this.location.trim()) {
                    alert('Mohon masukkan lokasi pengiriman!');
                    return;
                }
                if (!this.locationInRange) {
                    alert('Lokasi di luar jangkauan 2 KM');
                    return;
                }
                this.isProcessing = true;
                try {
                    const response = await fetch(`{{ route('order.checkout') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            paymentMethod: paymentMethod, 
                            location: this.location,
                            ongkir: this.ongkir,
                            distance: this.distance,
                            lat: this.buyerCoords ? this.buyerCoords[0] : null,
                            lng: this.buyerCoords ? this.buyerCoords[1] : null
                        })
                    });
                    const data = await response.json();
                    
                    if (data.success && data.payment_url) {
                        window.location.href = data.payment_url;
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                        this.isProcessing = false;
                    }
                } catch (e) {
                    alert('Gagal menghubungi server');
                    this.isProcessing = false;
                }
            }
        }" x-init="initMap()">
            <!-- Cart Items -->
            <div class="card animate-fade-in-up">
                <h3>Daftar Item</h3>
                <div class="mt-2">
                    @foreach($order->items as $item)
                    <div class="flex items-center justify-between p-1" id="cart-item-{{ $item->id }}" style="border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 1rem; margin-bottom: 1rem;">
                        <div class="flex items-center gap-1">
                            <img src="{{ $item->menu->image_url ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=100' }}" style="width: 60px; height: 60px; border-radius: 8px; object-fit:cover;">
                            <div>
                                <h4 style="margin: 0;">{{ $item->menu->name ?? 'Menu dihapus' }}</h4>
                                <div style="color: var(--primary); font-weight: bold;">Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-1">
                            <button @click="updateQuantity({{ $item->id }}, 'decrease')" class="btn btn-outline" style="padding: 0.2rem 0.6rem;">-</button>
                            
                            <span id="item-qty-{{ $item->id }}" style="font-weight: bold; width: 30px; text-align: center;">{{ $item->quantity }}</span>
                            
                            <button @click="updateQuantity({{ $item->id }}, 'increase')" class="btn btn-outline" style="padding: 0.2rem 0.6rem;">+</button>

                            <button @click="removeItem({{ $item->id }})" class="action-btn delete" style="margin-left: 1rem;"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card animate-fade-in-up delay-100" style="align-self: start;">
                <h3>Ringkasan Pembayaran</h3>
                
                <!-- Pricing Breakdown -->
                <div class="flex justify-between mt-2 mb-1">
                    <span style="color: var(--text-muted);">Subtotal Menu:</span>
                    <span style="font-weight: bold;">Rp <span x-text="totalPrice"></span></span>
                </div>

                <div class="flex justify-between mt-1 mb-1">
                    <span style="color: var(--text-muted);">Ongkos Kirim:</span>
                    <span style="font-weight: bold;" x-show="ongkir > 0">
                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(ongkir)"></span> 
                        <span style="font-size: 0.8rem; font-weight: normal; color: var(--text-muted);">
                            (<span x-text="distance"></span> Km)
                        </span>
                    </span>
                    <span style="font-weight: bold; color: var(--text-muted);" x-show="ongkir === 0">
                        Menghitung...
                    </span>
                </div>

                <hr style="border: 0; border-top: 1px solid rgba(0,0,0,0.08); margin: 0.75rem 0;">

                <div class="flex justify-between mt-1 mb-2" style="font-size: 1.2rem;">
                    <span style="font-weight: bold;">Total Bayar:</span>
                    <span style="font-weight: 900; color: var(--primary);">
                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(basePrice + ongkir)"></span>
                    </span>
                </div>

                <!-- Map Container -->
                <div class="mb-2" style="z-index: 1;">
                    <label class="label">Pilih Lokasi Pengiriman di Peta</label>
                    <div id="map" style="height: 220px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.1); margin-top: 0.5rem; overflow: hidden; box-shadow: var(--shadow-subtle);"></div>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.3rem;">
                        <i class="fa-solid fa-circle-info"></i> Geser penanda biru ke lokasi Anda atau klik area di peta.
                    </small>
                    <div class="range-alert" :class="locationInRange ? 'range-ok' : 'range-error'" style="margin-top:0.75rem;">
                        <i class="fa-solid" :class="locationInRange ? 'fa-circle-check' : 'fa-triangle-exclamation'"></i>
                        <span x-text="distanceMessage"></span>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="label">Alamat Pengiriman (Otomatis dari Peta) *</label>
                    <textarea x-model="location" class="input" rows="2" placeholder="Masukkan alamat lengkap atau detail lokasi untuk diantar..." required></textarea>
                </div>

                <div class="mb-2">
                    <label class="label">Pilih Metode Pembayaran</label>
                    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 0.5rem;">
                        <label class="card" :style="paymentMethod === 'SP' ? 'border-color: var(--accent); background: rgba(0,210,211,0.05);' : 'cursor: pointer;'" @click="paymentMethod = 'SP'">
                            <input type="radio" name="payment_method" value="SP" x-model="paymentMethod" style="display: none;">
                            <div class="text-center">
                                <i class="fa-solid fa-qrcode" style="font-size: 1.5rem; color: var(--accent);"></i>
                                <div style="font-size: 0.8rem; font-weight: bold; margin-top: 0.5rem;">QRIS</div>
                            </div>
                        </label>
                        <label class="card" :style="paymentMethod === 'M1' ? 'border-color: var(--accent); background: rgba(0,210,211,0.05);' : 'cursor: pointer;'" @click="paymentMethod = 'M1'">
                            <input type="radio" name="payment_method" value="M1" x-model="paymentMethod" style="display: none;">
                            <div class="text-center">
                                <i class="fa-solid fa-building-columns" style="font-size: 1.5rem; color: var(--accent);"></i>
                                <div style="font-size: 0.8rem; font-weight: bold; margin-top: 0.5rem;">Virtual Account</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="label">Informasi Pembayaran</label>
                    <p class="text-sm mt-1" style="color: var(--text-muted);">
                        Pembayaran akan diproses melalui gateway aman Duitku.
                    </p>
                </div>

                <div class="mt-2">
                    <button @click="checkout(paymentMethod)" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1rem;" x-bind:disabled="isProcessing || !locationInRange">
                        <span x-show="!isProcessing"><i class="fa-solid fa-lock"></i> Bayar Sekarang</span>
                        <span x-show="isProcessing" style="display: none;"><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span>
                    </button>
                    <form id="payment-success-form" action="{{ route('payment.success') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
                <div class="text-center mt-1">
                    <small style="color: var(--text-muted);"><i class="fa-solid fa-shield-halved"></i> Pembayaran aman didukung oleh <strong>Duitku</strong></small>
                </div>
            </div>
        </div>
        @else
        <div class="card text-center animate-fade-in-up" style="padding: 4rem 2rem;">
            <i class="fa-solid fa-cart-shopping" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h3>Keranjang masih kosong</h3>
            <p>Yuk, lihat-lihat menu lezat kami!</p>
            <a href="{{ route('menu') }}" class="btn btn-primary mt-2">Pesan Sekarang</a>
        </div>
        @endif

    </div>
</section>
@endsection
