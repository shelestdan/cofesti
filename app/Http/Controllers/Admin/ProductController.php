<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $queryText = trim($request->string('q')->limit(120)->toString());
        $products = Product::query()
            ->with('categories')
            ->when($queryText !== '', fn ($query) => $query->where('name', 'like', '%'.$queryText.'%'))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'queryText'));
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(UpsertProductRequest $request): RedirectResponse
    {
        $product = Product::query()->create($this->attributes($request));
        $product->categories()->sync($request->safe()->array('category_ids'));

        return redirect()->route('admin.products.edit', $product)->with('status', 'Товар создан.');
    }

    public function edit(Product $product): View
    {
        $product->load('categories');

        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpsertProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($this->attributes($request));
        $product->categories()->sync($request->safe()->array('category_ids'));

        return redirect()->route('admin.products.edit', $product)->with('status', 'Товар обновлён.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['is_published' => false]);

        return redirect()->route('admin.products.index')->with('status', 'Товар снят с публикации.');
    }

    private function attributes(UpsertProductRequest $request): array
    {
        return [
            ...$request->safe()->except(['category_ids', 'is_purchasable', 'is_published', 'is_featured']),
            'is_purchasable' => $request->boolean('is_purchasable'),
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
        ];
    }
}
