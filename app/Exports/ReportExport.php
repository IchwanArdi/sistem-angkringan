<?php
// app/Exports/ReportExport.php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Order::where('user_id', auth()->id())
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'completed')
            ->with('orderItems.menu')
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'No. Pesanan',
            'Tanggal',
            'Nama Pelanggan',
            'Total',
            'Item',
            'Jumlah Item',
            'Catatan'
        ];
    }

    public function map($order): array
    {
        $items = $order->orderItems->map(function($item) {
            return $item->menu->nama_makanan . ' (x' . $item->quantity . ')';
        })->implode(', ');

        $totalItems = $order->orderItems->sum('quantity');

        return [
            $order->order_number,
            $order->created_at->format('d/m/Y H:i'),
            $order->customer_name ?: 'Tanpa Nama',
            $order->total_amount,
            $items,
            $totalItems,
            $order->notes ?: '-'
        ];
    }
}