@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard Angkringan</h1>
        <p class="text-gray-600 mt-2">Selamat datang, {{ Auth::user()->name }}!</p>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Menu -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-700">Total Menu</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $totalMenu }}</p>
                </div>
                <div class="text-blue-500">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pesanan Hari Ini -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-700">Pesanan Hari Ini</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $pesananHariIni }}</p>
                </div>
                <div class="text-green-500">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pemasukan Hari Ini -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-700">Pemasukan Hari Ini</h3>
                    <p class="text-3xl font-bold text-yellow-600">Rp {{ number_format($pemasukanHariIni, 0, ',', '.') }}</p>
                </div>
                <div class="text-yellow-500">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Pemasukan Bulan Ini -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-700">Pemasukan Bulan Ini</h3>
                    <p class="text-3xl font-bold text-purple-600">Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}</p>
                </div>
                <div class="text-purple-500">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="{{ route('menus.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg p-6 text-center transition duration-200">
            <div class="text-4xl mb-4">🍽️</div>
            <h3 class="text-xl font-semibold">Tambah Menu Baru</h3>
            <p class="text-blue-100 mt-2">Tambahkan makanan atau minuman baru</p>
        </a>

        <a href="{{ route('orders.create') }}" class="bg-green-500 hover:bg-green-600 text-white rounded-lg p-6 text-center transition duration-200">
            <div class="text-4xl mb-4">📝</div>
            <h3 class="text-xl font-semibold">Buat Pesanan Baru</h3>
            <p class="text-green-100 mt-2">Catat pesanan pelanggan baru</p>
        </a>

        <a href="{{ route('reports.index') }}" class="bg-purple-500 hover:bg-purple-600 text-white rounded-lg p-6 text-center transition duration-200">
            <div class="text-4xl mb-4">📊</div>
            <h3 class="text-xl font-semibold">Lihat Laporan</h3>
            <p class="text-purple-100 mt-2">Analisis penjualan dan keuangan</p>
        </a>
    </div>

    <!-- Pesanan Terbaru & Menu Favorit -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pesanan Terbaru -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Pesanan Terbaru</h3>
                <a href="{{ route('orders.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">Lihat Semua</a>
            </div>
            @if($pesananTerbaru->count() > 0)
                <div class="space-y-3">
                    @foreach($pesananTerbaru as $pesanan)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">#{{ $pesanan->id }}</p>
                                <p class="text-sm text-gray-600">{{ $pesanan->created_at->format('H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-green-600">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</p>
                                <p class="text-sm text-gray-600">{{ $pesanan->orderItems->count() }} item</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>Belum ada pesanan hari ini</p>
                </div>
            @endif
        </div>

        <!-- Menu Favorit/Terlaris -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Menu Terlaris</h3>
                <a href="{{ route('menus.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">Lihat Semua</a>
            </div>
            @if($menuTerlaris->count() > 0)
                <div class="space-y-3">
                    @foreach($menuTerlaris as $menu)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">{{ $menu->nama_makanan }}</p>
                                <p class="text-sm text-gray-600">{{ $menu->kategori }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-blue-600">{{ $menu->total_terjual ?? 0 }}x</p>
                                <p class="text-sm text-gray-600">Rp {{ number_format($menu->harga, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>Belum ada data penjualan</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Grafik Penjualan 7 Hari Terakhir (Optional) -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Penjualan 7 Hari Terakhir</h3>
        <div class="h-64 flex items-end justify-between space-x-2">
            @foreach($penjualan7Hari as $hari)
                <div class="flex-1 flex flex-col items-center">
                    <div class="bg-blue-500 rounded-t" style="height: {{ $hari['total'] > 0 ? ($hari['total'] / $maxPenjualan * 200) : 5 }}px; min-height: 5px;"></div>
                    <div class="mt-2 text-center">
                        <p class="text-xs text-gray-600">{{ $hari['tanggal'] }}</p>
                        <p class="text-sm font-semibold">Rp {{ number_format($hari['total'], 0, ',', '.') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection