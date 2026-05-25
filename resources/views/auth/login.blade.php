@extends('layouts.app')

@section('body_class', 'home-dashboard')

@section('content')
<section class="auth-page">
    <div class="auth-shell dashboard-container">
        <div class="auth-visual animate-fade-in-up">
            <span class="auth-pill">Private Kitchen Access</span>
            <h1>Masuk ke meja makan Ibu Ida.</h1>
            <p>Pesan nasi rames harian, pantau checkout, dan lanjutkan pesanan favorit dalam suasana dapur rumahan yang hangat.</p>

            <div class="auth-preview-card">
                <img src="https://images.unsplash.com/photo-1512058564366-18510be2db19?q=80&w=900&auto=format&fit=crop" alt="Nasi rames rumahan">
                <div>
                    <span>Signature Today</span>
                    <strong>Nasi Rames Heritage</strong>
                </div>
            </div>
        </div>

        <div class="auth-card animate-fade-in-up delay-100">
            <div class="auth-mark" aria-hidden="true">
                <span></span>
            </div>

            <div class="auth-heading">
                <span>Welcome Back</span>
                <h2>Login</h2>
                <p>Silakan masuk untuk melanjutkan pesananmu.</p>
            </div>

            @if($errors->any())
                <div class="auth-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf
                <div class="auth-field">
                    <label for="email">Email / Nama Akun</label>
                    <div class="auth-input-wrap">
                        <i class="fa-regular fa-user"></i>
                        <input id="email" type="text" name="email" required autofocus value="{{ old('email') }}" placeholder="nama@email.com">
                    </div>
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="auth-input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input id="password" type="password" name="password" required placeholder="Masukkan password">
                    </div>
                </div>

                <button type="submit" class="auth-submit">
                    <span>Masuk Sekarang</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <p class="auth-switch">
                Belum punya akun?
                <a href="{{ route('register') }}">Daftar di sini</a>
            </p>
        </div>
    </div>
</section>
@endsection
