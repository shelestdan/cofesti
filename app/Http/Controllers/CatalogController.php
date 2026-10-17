<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $queryText = trim($request->string('q')->limit(120)->toString());
        $selectedCategory = $request->string('category')->toString();
        $sort = $request->string('sort')->toString();
        $categories = Category::query()
            ->published()
            ->whereNull('parent_id')
            ->orderBy('position')
            ->orderBy('name')
            ->get();
        $products = Product::query()
            ->published()
            ->with(['categories', 'media'])
            ->when($selectedCategory !== '', function (Builder $query) use ($selectedCategory): Builder {
                return $query->whereHas('categories', fn (Builder $categoriesQuery): Builder => $categoriesQuery->where('slug', $selectedCategory));
            })
            ->when($queryText !== '', function (Builder $query) use ($queryText): Builder {
                return $query->where('name', 'like', '%'.$queryText.'%');
            });

        match ($sort) {
            'price_asc' => $products->orderBy('price_minor')->orderBy('id'),
            'price_desc' => $products->orderByDesc('price_minor')->orderByDesc('id'),
            'name' => $products->orderBy('name')->orderBy('id'),
            default => $products->orderBy('name')->orderBy('id'),
        };

        return view('catalog.index', [
            'categories' => $categories,
            'products' => $products->paginate(24)->withQueryString(),
            'queryText' => $queryText,
            'selectedCategory' => $selectedCategory,
            'sort' => $sort,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_published, 404);

        $product->load(['categories', 'media', 'variants']);
        $relatedProducts = Product::query()
            ->published()
            ->where('id', '!=', $product->id)
            ->whereHas('categories', fn (Builder $query): Builder => $query->whereIn('categories.id', $product->categories->modelKeys()))
            ->with(['categories', 'media'])
            ->orderBy('name')
            ->limit(4)
            ->get();

        return view('catalog.show', compact('product', 'relatedProducts'));
    }
}
