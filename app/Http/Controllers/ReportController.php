<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;
use PDF;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $orders = Order::where('user_id', auth()->id())
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->with('orderItems.menu')
            ->latest()
            ->get();

        $totalIncome = $orders->sum('total_amount');
        $totalOrders = $orders->count();

        // Statistik per kategori
        $categoryStats = [];
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $category = $item->menu->kategori;
                if (!isset($categoryStats[$category])) {
                    $categoryStats[$category] = [
                        'quantity' => 0,
                        'income' => 0
                    ];
                }
                $categoryStats[$category]['quantity'] += $item->quantity;
                $categoryStats[$category]['income'] += $item->subtotal;
            }
        }

        // Data untuk chart harian
        $dailyIncome = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dayIncome = $orders->where('created_at', '>=', $currentDate->startOfDay())
                              ->where('created_at', '<=', $currentDate->endOfDay())
                              ->sum('total_amount');
            
            $dailyIncome[] = [
                'date' => $currentDate->format('Y-m-d'),
                'income' => $dayIncome
            ];
            
            $currentDate->addDay();
        }

        return view('reports.index', compact(
            'orders',
            'totalIncome',
            'totalOrders',
            'categoryStats',
            'dailyIncome',
            'startDate',
            'endDate'
        ));
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        return Excel::download(
            new ReportExport($startDate, $endDate), 
            'laporan-angkringan-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportPDF(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $orders = Order::where('user_id', auth()->id())
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->with('orderItems.menu')
            ->latest()
            ->get();

        $totalIncome = $orders->sum('total_amount');
        $totalOrders = $orders->count();

        $pdf = PDF::loadView('reports.pdf', compact('orders', 'totalIncome', 'totalOrders', 'startDate', 'endDate'));
        
        return $pdf->download('laporan-angkringan-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf');
    }
}