<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertCafeMenuItemRequest;
use App\Models\CafeMenuItem;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CafeMenuItemController extends Controller
{
    public function index(): View
    {
        $items = CafeMenuItem::query()->with('media')->orderBy('position')->paginate(30);

        return view('admin.cafe-menu.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.cafe-menu.form', [
            'cafeMenuItem' => new CafeMenuItem,
            'media' => Media::query()->orderByDesc('id')->limit(100)->get(),
        ]);
    }

    public function store(UpsertCafeMenuItemRequest $request): RedirectResponse
    {
        $item = CafeMenuItem::query()->create($this->attributes($request));

        return redirect()->route('admin.cafe-menu-items.edit', $item)->with('status', 'Позиция меню создана.');
    }

    public function edit(CafeMenuItem $cafeMenuItem): View
    {
        return view('admin.cafe-menu.form', [
            'cafeMenuItem' => $cafeMenuItem,
            'media' => Media::query()->orderByDesc('id')->limit(100)->get(),
        ]);
    }

    public function update(UpsertCafeMenuItemRequest $request, CafeMenuItem $cafeMenuItem): RedirectResponse
    {
        $cafeMenuItem->update($this->attributes($request));

        return redirect()->route('admin.cafe-menu-items.edit', $cafeMenuItem)->with('status', 'Позиция меню обновлена.');
    }

    public function destroy(CafeMenuItem $cafeMenuItem): RedirectResponse
    {
        $cafeMenuItem->update([
            'is_available' => false,
            'is_published' => false,
        ]);

        return redirect()->route('admin.cafe-menu-items.index')->with('status', 'Позиция снята с публикации.');
    }

    private function attributes(UpsertCafeMenuItemRequest $request): array
    {
        return [
            ...$request->safe()->except(['is_available', 'is_published']),
            'is_available' => $request->boolean('is_available'),
            'is_published' => $request->boolean('is_published'),
        ];
    }
}
