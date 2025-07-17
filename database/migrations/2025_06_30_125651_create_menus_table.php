<?php
// database/migrations/xxxx_create_menus_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_makanan');
            $table->decimal('harga', 10, 2);
            $table->enum('kategori', ['Nasi', 'Gorengan', 'Minuman', 'Jajanan', 'Lainnya']);
            $table->integer('stok')->default(0);
            $table->boolean('is_available')->default(true);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};