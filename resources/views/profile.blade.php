@extends('layouts.app')

@section('body_class', 'home-dashboard')

@section('content')
<section class="profile-page" x-data="{ section: 'account' }">
    <div class="profile-shell dashboard-container">
        <aside class="profile-sidebar animate-fade-in-up">
            <div class="profile-avatar">
                <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            </div>

            <h1>{{ $user->name }}</h1>
            <span class="profile-tier">
                <i class="fa-solid fa-award"></i>
                Layanan Premium
            </span>

            <nav class="profile-nav" aria-label="Navigasi profil">
                <button type="button" :class="{ 'active': section === 'account' }" @click="section = 'account'">
                    <i class="fa-regular fa-user"></i>
                    <span>Informasi Akun</span>
                </button>
                <a href="{{ route('orders.my') }}">
                    <i class="fa-regular fa-clipboard"></i>
                    <span>Riwayat Pesanan</span>
                </a>
                <a href="{{ route('menu') }}">
                    <i class="fa-regular fa-heart"></i>
                    <span>Menu Tersimpan</span>
                </a>
                <a href="{{ route('cart') }}">
                    <i class="fa-regular fa-credit-card"></i>
                    <span>Metode Pembayaran</span>
                </a>
                <button type="button" :class="{ 'active': section === 'settings' }" @click="section = 'settings'">
                    <i class="fa-solid fa-gear"></i>
                    <span>Pengaturan</span>
                </button>
            </nav>
        </aside>

        <div class="profile-content">
            <section class="profile-card animate-fade-in-up delay-100" x-show="section === 'account'">
                <div class="profile-heading">
                    <h2>Informasi Akun</h2>
                    <p>Kelola detail akun Anda untuk pengalaman kuliner yang lebih personal dan layanan pengantaran yang lebih cepat.</p>
                </div>

                @if(session('success'))
                    <div class="profile-alert">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="profile-alert profile-alert-error">{{ $errors->first() }}</div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST" class="profile-form">
                    @csrf
                    <div class="profile-field">
                        <label for="name">Nama Lengkap</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="profile-field">
                        <label for="email">Alamat Email</label>
                        <div class="profile-readonly">
                            <input id="email" type="email" value="{{ $user->email }}" readonly>
                            <i class="fa-solid fa-lock"></i>
                        </div>
                    </div>

                    <div class="profile-field">
                        <label for="phone">Nomor HP</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+62 812 3456 7890">
                    </div>

                    <div class="profile-divider"></div>

                    <div class="profile-field">
                        <label for="password">Kata Sandi Baru (Opsional)</label>
                        <input id="password" type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                    </div>

                    <div class="profile-field">
                        <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                        <input id="password_confirmation" type="password" name="password_confirmation">
                    </div>

                    <div class="profile-divider wide"></div>

                    <div class="profile-form-footer">
                        <em>Terakhir diperbarui: {{ optional($user->updated_at)->format('d F Y') }}</em>
                        <button type="submit">Perbarui Profil</button>
                    </div>
                </form>
            </section>

            <section class="profile-card profile-settings-card animate-fade-in-up delay-100" x-show="section === 'settings'" style="display: none;">
                <div class="profile-heading">
                    <h2>Pengaturan Tampilan</h2>
                    <p>Pilih nuansa visual dan tingkat animasi yang paling nyaman untuk digunakan sehari-hari.</p>
                </div>

                <div class="profile-settings-list">
                    <div class="profile-setting-row">
                        <div>
                            <h3>Tema Website</h3>
                            <p>Tema gelap memberi nuansa premium. Tema terang membuat tampilan lebih bersih dan cerah.</p>
                        </div>
                        <div class="profile-theme-switch">
                            <button type="button" :class="{ 'active': $store.preferences.theme === 'dark' }" @click="$store.preferences.setTheme('dark')">
                                <i class="fa-solid fa-moon"></i>
                                <span>Gelap</span>
                            </button>
                            <button type="button" :class="{ 'active': $store.preferences.theme === 'light' }" @click="$store.preferences.setTheme('light')">
                                <i class="fa-solid fa-sun"></i>
                                <span>Terang</span>
                            </button>
                        </div>
                    </div>

                    <div class="profile-setting-row">
                        <div>
                            <h3>Kurangi Animasi</h3>
                            <p>Matikan gerakan marquee, transisi, dan animasi masuk jika ingin pengalaman yang lebih tenang.</p>
                        </div>
                        <button
                            type="button"
                            class="profile-toggle"
                            :class="{ 'active': $store.preferences.reduceMotion }"
                            @click="$store.preferences.toggleReduceMotion()"
                            :aria-pressed="$store.preferences.reduceMotion.toString()"
                        >
                            <span></span>
                        </button>
                    </div>
                </div>
            </section>

            <div class="profile-assist-grid animate-fade-in-up delay-200">
                <article>
                    <i class="fa-solid fa-shield-halved"></i>
                    <div>
                        <h3>Otentikasi Dua Faktor</h3>
                        <p>Tingkatkan keamanan akun Anda sekarang.</p>
                    </div>
                </article>
                <article>
                    <i class="fa-regular fa-circle-question"></i>
                    <div>
                        <h3>Butuh Bantuan?</h3>
                        <p>Hubungi tim Kantin Ibu Ida kapan saja.</p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
