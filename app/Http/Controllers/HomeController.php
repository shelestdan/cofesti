<?php

namespace App\Http\Controllers;

use App\Models\CafeMenuItem;
use App\Models\Category;
use App\Models\Product;
use App\StockStatus;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::query()
            ->published()
            ->whereNull('parent_id')
            ->orderBy('position')
            ->orderBy('name')
            ->get();
        $featuredProducts = Product::query()
            ->published()
            ->where('stock_status', StockStatus::Available->value)
            ->with(['categories', 'media'])
            ->orderBy('name')
            ->limit(6)
            ->get();
        $cafeMenuItems = CafeMenuItem::query()
            ->where('is_published', true)
            ->where('is_available', true)
            ->with('media')
            ->orderBy('position')
            ->limit(6)
            ->get();

        return view('home', compact('categories', 'featuredProducts', 'cafeMenuItems'));
    }
}
