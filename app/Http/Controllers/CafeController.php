<?php

namespace App\Http\Controllers;

use App\Models\CafeMenuItem;
use Illuminate\View\View;

class CafeController extends Controller
{
    public function index(): View
    {
        $menuItems = CafeMenuItem::query()
            ->where('is_published', true)
            ->where('is_available', true)
            ->with('media')
            ->orderBy('position')
            ->get();

        return view('cafe.index', compact('menuItems'));
    }
}
