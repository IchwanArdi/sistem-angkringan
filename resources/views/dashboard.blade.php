@extends('layouts.app')

@section('title', 'Dashboard - Sistem Angkringan')

@section('content')
<div class="dashboard-container">
    <div class="container">
        <!-- Welcome Section -->
        <div class="dashboard-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="bi bi-speedometer2 text-primary"></i> 
                        Selamat Datang, {{ Auth::user()->name }}!
                    </h1>
                    <p class="text-muted mb-3">
                        <i class="bi bi-shop"></i> <strong>{{ Auth::user()->angkringan_name }}</strong><br>
                        <i class="bi bi-geo-alt"></i> {{ Auth::user()->address }}
                    </p>
                    <p class="lead">Kelola angkringan Anda dengan mudah melalui sistem ini.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px;">
                        <i class="bi bi-shop display-4 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-currency-dollar display-6 text-success"></i>
                    </div>
                    <h3 class="h4 mb-1">Rp 0</h3>
                    <p class="text-muted mb-0">Penjualan Hari Ini</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-bag-check display-6 text-info"></i>
                    </div>
                    <h3 class="h4 mb-1">0</h3>
                    <p class="text-muted mb-0">Pesanan Hari Ini</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-menu-button-wide display-6 text-warning"></i>
                    </div>
                    <h3 class="h4 mb-1">0</h3>
                    <p class="text-muted mb-0">Menu Tersedia</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-star display-6 text-danger"></i>
                    </div>
                    <h3 class="h4 mb-1">-</h3>
                    <p class="text-muted mb-0">Menu Terlaris</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card">
            <h2 class="h4 mb-4">
                <i class="bi bi-lightning"></i> Aksi Cepat
            </h2>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <a href="#" class="btn btn-outline-primary w-100 p-3 text-start">
                        <i class="bi bi-plus-circle me-3 fs-4"></i>
                        <div>
                            <strong>Tambah Menu</strong>
                            <p class="mb-0 small text-muted">Tambah menu baru untuk hari ini</p>
                        </div>
                    </a>
                </div>
                
                <div class="col-md-4 mb-3">
                    <a href="#" class="btn btn-outline-success w-100 p-3 text-start">
                        <i class="bi bi-cart-plus me-3 fs-4"></i>
                        <div>
                            <strong>Catat Pesanan</strong>
                            <p class="mb-0 small text-muted">Catat pesanan dari pelanggan</p>
                        </div>
                    </a>
                </div>
                
                <div class="col-md-4 mb-3">
                    <a href="#" class="btn btn-outline-info w-100 p-3 text-start">
                        <i class="bi bi-graph-up me-3 fs-4"></i>
                        <div>
                            <strong>Lihat Laporan</strong>
                            <p class="mb-0 small text-muted">Lihat laporan penjualan</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity (placeholder) -->
        <div class="dashboard-card">
            <h2 class="h4 mb-4">
                <i class="bi bi-clock-history"></i> Aktivitas Terbaru
            </h2>
            
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                <h3 class="h5 text-muted">Belum ada aktivitas</h3>
                <p class="text-muted">Mulai dengan menambah menu atau mencatat pesanan pertama Anda.</p>
            </div>
        </div>
    </div>
</div>
@endsection