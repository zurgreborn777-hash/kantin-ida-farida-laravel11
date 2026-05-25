@extends('layouts.app')

@section('body_class', 'home-dashboard')

@section('content')
<!-- Leaflet Map CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Integrasi Pembayaran Duitku -->

<section class="cart-page">
    <div class="dashboard-container">
        <div class="cart-hero animate-fade-in-up">
            <span>Checkout Aman</span>
            <h1>Keranjang Pesanan</h1>
            <p>Periksa hidangan pilihanmu, tentukan lokasi antar, lalu lanjutkan pembayaran dengan aman.</p>
        </div>
        
        @if(session('success'))
            <div class="cart-alert">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="cart-alert cart-alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        @if($order && $order->items->count() > 0)
        <div class="cart-layout" x-data="{ 
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
                                style: { color: getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim() || 'var(--primary)', weight: 5, opacity: 0.7 }
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
            <div class="cart-items-panel animate-fade-in-up">
                <div class="cart-panel-head">
                    <div>
                        <span>Periksa Pesanan</span>
                        <h2>Daftar Item</h2>
                    </div>
                    <a href="{{ route('menu') }}">Tambah Menu</a>
                </div>

                <div class="cart-items-list">
                    @foreach($order->items as $item)
                    <article class="cart-item-row" id="cart-item-{{ $item->id }}">
                        <div class="cart-item-main">
                            <img src="{{ $item->menu->image_url ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=200&auto=format&fit=crop' }}" alt="{{ $item->menu->name ?? 'Menu dihapus' }}">
                            <div>
                                <span>{{ $item->menu->category ?? 'Menu Harian' }}</span>
                                <h3>{{ $item->menu->name ?? 'Menu dihapus' }}</h3>
                                <strong>Rp {{ number_format($item->price, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="cart-item-actions">
                            <div class="cart-qty">
                                <button @click="updateQuantity({{ $item->id }}, 'decrease')" type="button">-</button>
                                <span id="item-qty-{{ $item->id }}">{{ $item->quantity }}</span>
                                <button @click="updateQuantity({{ $item->id }}, 'increase')" type="button">+</button>
                            </div>
                            <button @click="removeItem({{ $item->id }})" class="cart-remove" type="button" aria-label="Hapus {{ $item->menu->name ?? 'item' }}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>

            <aside class="cart-summary-panel animate-fade-in-up delay-100">
                <div class="cart-panel-head compact">
                    <div>
                        <span>Ringkasan Pembayaran</span>
                        <h2>Ringkasan</h2>
                    </div>
                </div>

                <div class="cart-price-line">
                    <span>Subtotal Menu</span>
                    <strong>Rp <span x-text="totalPrice"></span></strong>
                </div>

                <div class="cart-price-line">
                    <span>Ongkos Kirim</span>
                    <strong x-show="ongkir > 0">
                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(ongkir)"></span> 
                        <small>
                            (<span x-text="distance"></span> Km)
                        </small>
                    </strong>
                    <strong x-show="ongkir === 0">
                        Menghitung...
                    </strong>
                </div>

                <div class="cart-total-line">
                    <span>Total Bayar</span>
                    <strong>
                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(basePrice + ongkir)"></span>
                    </strong>
                </div>

                <div class="cart-field">
                    <label>Pilih Lokasi Pengiriman di Peta</label>
                    <div id="map" class="cart-map"></div>
                    <small>
                        <i class="fa-solid fa-circle-info"></i> Geser penanda biru ke lokasi Anda atau klik area di peta.
                    </small>
                    <div class="cart-range-alert" :class="locationInRange ? 'range-ok' : 'range-error'">
                        <i class="fa-solid" :class="locationInRange ? 'fa-circle-check' : 'fa-triangle-exclamation'"></i>
                        <span x-text="distanceMessage"></span>
                    </div>
                </div>

                <div class="cart-field">
                    <label>Alamat Pengiriman *</label>
                    <textarea x-model="location" rows="2" placeholder="Masukkan alamat lengkap atau detail lokasi untuk diantar..." required></textarea>
                </div>

                <div class="cart-field">
                    <label>Pilih Metode Pembayaran</label>
                    <div class="cart-payment-grid">
                        <label class="cart-payment-option" :class="{ 'active': paymentMethod === 'SP' }" @click="paymentMethod = 'SP'">
                            <input type="radio" name="payment_method" value="SP" x-model="paymentMethod" style="display: none;">
                            <i class="fa-solid fa-qrcode"></i>
                            <span>QRIS</span>
                        </label>
                        <label class="cart-payment-option" :class="{ 'active': paymentMethod === 'M1' }" @click="paymentMethod = 'M1'">
                            <input type="radio" name="payment_method" value="M1" x-model="paymentMethod" style="display: none;">
                            <i class="fa-solid fa-building-columns"></i>
                            <span>Virtual Account</span>
                        </label>
                    </div>
                </div>

                <p class="cart-payment-note">
                    <i class="fa-solid fa-shield-halved"></i>
                    Pembayaran akan diproses melalui gateway aman Duitku.
                </p>

                <div>
                    <button @click="checkout(paymentMethod)" class="cart-checkout-btn" x-bind:disabled="isProcessing || !locationInRange">
                        <span x-show="!isProcessing"><i class="fa-solid fa-lock"></i> Bayar Sekarang</span>
                        <span x-show="isProcessing" style="display: none;"><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span>
                    </button>
                    <form id="payment-success-form" action="{{ route('payment.success') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </aside>
        </div>
        @else
        <div class="cart-empty animate-fade-in-up">
            <i class="fa-solid fa-cart-shopping"></i>
            <h3>Keranjang masih kosong</h3>
            <p>Yuk, lihat-lihat menu lezat kami!</p>
            <a href="{{ route('menu') }}">Pesan Sekarang</a>
        </div>
        @endif

    </div>
</section>
@endsection
