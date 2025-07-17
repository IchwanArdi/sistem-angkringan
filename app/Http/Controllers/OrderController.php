<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Menu;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('orderItems.menu')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $menus = Menu::where('user_id', auth()->id())
            ->available()
            ->orderBy('kategori')
            ->orderBy('nama_makanan')
            ->get();

        return view('orders.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;

            // Hitung total amount terlebih dahulu
            foreach ($request->items as $item) {
                $menu = Menu::find($item['menu_id']);
                $subtotal = $menu->harga * $item['quantity'];
                $totalAmount += $subtotal;
            }

            // Buat order
            $order = Order::create([
                'customer_name' => $request->customer_name,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
                'status' => 'completed'
            ]);

            // Buat order items
            foreach ($request->items as $item) {
                $menu = Menu::find($item['menu_id']);
                $subtotal = $menu->harga * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'quantity' => $item['quantity'],
                    'price' => $menu->harga,
                    'subtotal' => $subtotal
                ]);

                // Update stok jika perlu
                if ($menu->stok > 0) {
                    $menu->decrement('stok', $item['quantity']);
                }
            }
        });

        return redirect()->route('orders.index')
            ->with('success', 'Pesanan berhasil dibuat!');
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        
        $order->load('orderItems.menu');
        return view('orders.show', compact('order'));
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);
        
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Pesanan berhasil dihapus!');
    }
}