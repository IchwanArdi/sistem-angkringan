<?php
// app/Http/Controllers/MenuController.php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::where('user_id', auth()->id())
            ->orderBy('kategori')
            ->orderBy('nama_makanan')
            ->paginate(10);

        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        return view('menus.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_makanan' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'kategori' => 'required|in:Nasi,Gorengan,Minuman,Jajanan,Lainnya',
            'stok' => 'nullable|integer|min:0'
        ]);

        Menu::create([
            'nama_makanan' => $request->nama_makanan,
            'harga' => $request->harga,
            'kategori' => $request->kategori,
            'stok' => $request->stok ?? 0,
            'user_id' => auth()->id()
        ]);

        return redirect()->route('menus.index')
            ->with('success', 'Menu berhasil ditambahkan!');
    }

    public function edit(Menu $menu)
    {
        $this->authorize('update', $menu);
        return view('menus.edit', compact('menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $this->authorize('update', $menu);

        $request->validate([
            'nama_makanan' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'kategori' => 'required|in:Nasi,Gorengan,Minuman,Jajanan,Lainnya',
            'stok' => 'nullable|integer|min:0',
            'is_available' => 'boolean'
        ]);

        $menu->update([
            'nama_makanan' => $request->nama_makanan,
            'harga' => $request->harga,
            'kategori' => $request->kategori,
            'stok' => $request->stok ?? 0,
            'is_available' => $request->has('is_available')
        ]);

        return redirect()->route('menus.index')
            ->with('success', 'Menu berhasil diperbarui!');
    }

    public function destroy(Menu $menu)
    {
        $this->authorize('delete', $menu);
        
        $menu->delete();

        return redirect()->route('menus.index')
            ->with('success', 'Menu berhasil dihapus!');
    }
}