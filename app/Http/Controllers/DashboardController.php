<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik dasar
        $totalMenu = Menu::count();
        $pesananHariIni = Order::whereDate('created_at', today())->count();
        $pemasukanHariIni = Order::whereDate('created_at', today())->sum('total_harga');
        $pemasukanBulanIni = Order::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->sum('total_harga');

        // Pesanan terbaru (5 pesanan terakhir hari ini)
        $pesananTerbaru = Order::with('orderItems.menu')
                              ->whereDate('created_at', today())
                              ->orderBy('created_at', 'desc')
                              ->take(5)
                              ->get();

        // Menu terlaris (berdasarkan jumlah penjualan)
        $menuTerlaris = Menu::select('menus.*', DB::raw('COALESCE(SUM(order_items.jumlah), 0) as total_terjual'))
                          ->leftJoin('order_items', 'menus.id', '=', 'order_items.menu_id')
                          ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
                          ->whereDate('orders.created_at', '>=', now()->subDays(7))
                          ->groupBy('menus.id', 'menus.nama_makanan', 'menus.harga', 'menus.kategori', 'menus.stok', 'menus.created_at', 'menus.updated_at')
                          ->orderBy('total_terjual', 'desc')
                          ->take(5)
                          ->get();

        // Data penjualan 7 hari terakhir untuk grafik
        $penjualan7Hari = [];
        $maxPenjualan = 0;
        
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = now()->subDays($i);
            $total = Order::whereDate('created_at', $tanggal->toDateString())->sum('total_harga');
            
            $penjualan7Hari[] = [
                'tanggal' => $tanggal->format('d/m'),
                'total' => $total
            ];
            
            if ($total > $maxPenjualan) {
                $maxPenjualan = $total;
            }
        }

        // Jika max penjualan 0, set ke 1 untuk menghindari pembagian dengan 0
        if ($maxPenjualan == 0) {
            $maxPenjualan = 1;
        }

        return view('dashboard', compact(
            'totalMenu',
            'pesananHariIni',
            'pemasukanHariIni',
            'pemasukanBulanIni',
            'pesananTerbaru',
            'menuTerlaris',
            'penjualan7Hari',
            'maxPenjualan'
        ));
    }
}