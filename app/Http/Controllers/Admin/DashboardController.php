<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeMenuItem;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'productCount' => Product::count(),
            'orderCount' => Order::count(),
            'inquiryCount' => Inquiry::count(),
            'cafeMenuItemCount' => CafeMenuItem::count(),
        ]);
    }
}
