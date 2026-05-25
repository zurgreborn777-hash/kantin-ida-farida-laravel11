@extends('layouts.app')

@section('content')
<section class="hero" style="min-height: 80vh;">
    <div class="container flex" style="justify-content: center;">
        <div class="card animate-fade-in-up" style="max-width: 400px; width: 100%;">
            <div class="text-center mb-2">
                <h2>Daftar Akun Baru</h2>
                <p>Mulai kumpulkan poin pesananmu!</p>
            </div>
            
            @if($errors->any())
                <div style="color: var(--primary); margin-bottom: 1rem; font-size: 0.9rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label class="label">Nama Lengkap</label>
                    <input type="text" name="name" class="input" required autofocus value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label class="label">Alamat Email</label>
                    <input type="email" name="email" class="input" required value="{{ old('email') }}">
                </div>
                
                <div class="form-group">
                    <label class="label">Kata Sandi</label>
                    <input type="password" name="password" class="input" required>
                </div>

                <div class="form-group">
                    <label class="label">Konfirmasi Kata Sandi</label>
                    <input type="password" name="password_confirmation" class="input" required>
                </div>
                
                <button type="submit" class="btn btn-secondary" style="width: 100%; margin-top: 1rem;">Daftar</button>
            </form>
            
            <div class="text-center mt-2">
                <p>Sudah punya akun? <a href="{{ route('login') }}" style="color: var(--secondary); font-weight: bold;">Masuk di sini</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
