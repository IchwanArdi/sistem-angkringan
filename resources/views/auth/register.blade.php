@extends('layouts.app')

@section('title', 'Daftar - Sistem Angkringan')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="max-width: 500px;">
        <div class="auth-header">
            <h1><i class="bi bi-shop"></i> Daftar Angkringan</h1>
            <p>Daftarkan angkringan Anda ke sistem</p>
        </div>
        
        <div class="auth-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" placeholder="Nama Lengkap" 
                                   value="{{ old('name') }}" required>
                            <label for="name"><i class="bi bi-person"></i> Nama Lengkap</label>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" placeholder="No. HP" 
                                   value="{{ old('phone') }}">
                            <label for="phone"><i class="bi bi-phone"></i> No. HP (Opsional)</label>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" placeholder="name@example.com" 
                           value="{{ old('email') }}" required>
                    <label for="email"><i class="bi bi-envelope"></i> Email</label>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Password" required>
                            <label for="password"><i class="bi bi-lock"></i> Password</label>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" 
                                   placeholder="Konfirmasi Password" required>
                            <label for="password_confirmation"><i class="bi bi-lock-fill"></i> Konfirmasi Password</label>
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control @error('angkringan_name') is-invalid @enderror" 
                           id="angkringan_name" name="angkringan_name" placeholder="Nama Angkringan" 
                           value="{{ old('angkringan_name') }}" required>
                    <label for="angkringan_name"><i class="bi bi-shop"></i> Nama Angkringan</label>
                    @error('angkringan_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <textarea class="form-control @error('address') is-invalid @enderror" 
                              id="address" name="address" placeholder="Alamat Angkringan" 
                              style="height: 100px" required>{{ old('address') }}</textarea>
                    <label for="address"><i class="bi bi-geo-alt"></i> Alamat Angkringan</label>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Daftar Sekarang
                    </button>
                </div>
            </form>

            <div class="text-center">
                <p class="mb-0">Sudah punya akun? 
                    <a href="{{ route('login') }}">Masuk di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
