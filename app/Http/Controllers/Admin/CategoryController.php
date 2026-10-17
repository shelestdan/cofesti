<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->with('parent')
            ->withCount(['children', 'products'])
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category,
            'parentCategories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(UpsertCategoryRequest $request): RedirectResponse
    {
        Category::query()->create($this->attributes($request));

        return redirect()->route('admin.categories.index')->with('status', 'Категория создана.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parentCategories' => Category::query()->where('id', '!=', $category->id)->orderBy('name')->get(),
        ]);
    }

    public function update(UpsertCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($this->attributes($request));

        return redirect()->route('admin.categories.index')->with('status', 'Категория обновлена.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists() || $category->products()->exists()) {
            return back()->withErrors(['category' => 'Сначала перенесите товары и дочерние категории.']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Категория удалена.');
    }

    private function attributes(UpsertCategoryRequest $request): array
    {
        return [
            ...$request->safe()->except('is_published'),
            'is_published' => $request->boolean('is_published'),
        ];
    }
}
