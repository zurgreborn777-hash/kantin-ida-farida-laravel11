@extends('layouts.app')

@section('body_class', 'home-dashboard')

@section('content')
<section class="auth-page">
    <div class="auth-shell dashboard-container">
        <div class="auth-visual animate-fade-in-up">
            <span class="auth-pill">Gabung Dapur Ibu Ida</span>
            <h1>Daftar ke meja makan Ibu Ida.</h1>
            <p>Mulai kumpulkan poin pesananmu, pesan nasi rames harian, pantau checkout, dan nikmati hidangan rumahan hangat.</p>

            <div class="auth-preview-card">
                <img src="https://images.unsplash.com/photo-1512058564366-18510be2db19?q=80&w=900&auto=format&fit=crop" alt="Nasi rames rumahan">
                <div>
                    <span>Andalan Hari Ini</span>
                    <strong>Nasi Rames Warisan</strong>
                </div>
            </div>
        </div>

        <div class="auth-card animate-fade-in-up delay-100">
            <div class="auth-mark" aria-hidden="true">
                <span></span>
            </div>

            <div class="auth-heading">
                <span>Selamat Datang</span>
                <h2>Daftar</h2>
                <p>Mulai kumpulkan poin pesananmu!</p>
            </div>

            @if($errors->any())
                <div class="auth-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf
                <div class="auth-field">
                    <label for="name">Nama Lengkap</label>
                    <div class="auth-input-wrap">
                        <i class="fa-regular fa-user"></i>
                        <input id="name" type="text" name="name" required autofocus value="{{ old('name') }}" placeholder="Nama Lengkap Anda">
                    </div>
                </div>

                <div class="auth-field">
                    <label for="email">Alamat Email</label>
                    <div class="auth-input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input id="email" type="email" name="email" required value="{{ old('email') }}" placeholder="nama@email.com">
                    </div>
                </div>

                <div class="auth-field">
                    <label for="password">Kata Sandi</label>
                    <div class="auth-input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input id="password" type="password" name="password" required placeholder="Buat kata sandi">
                    </div>
                </div>

                <div class="auth-field">
                    <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                    <div class="auth-input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Ulangi kata sandi">
                    </div>
                </div>

                <button type="submit" class="auth-submit">
                    <span>Daftar Sekarang</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <p class="auth-switch">
                Sudah punya akun?
                <a href="{{ route('login') }}">Masuk di sini</a>
            </p>
        </div>
    </div>
</section>
@endsection

