@extends('layouts.admin')

@section('content')
    @php($editing = $product->exists)
    <p class="eyebrow">Каталог</p>
    <h1 class="display-title mt-3 text-5xl">{{ $editing ? 'Изменить товар' : 'Новый товар' }}</h1>
    <form class="admin-card mt-8 grid max-w-3xl gap-5" method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <label><span class="mb-2 block text-sm font-bold">Название</span><input class="form-field" name="name" value="{{ old('name', $product->name) }}" required></label>
        <div class="grid gap-5 sm:grid-cols-2"><label><span class="mb-2 block text-sm font-bold">Адрес</span><input class="form-field" name="slug" value="{{ old('slug', $product->slug) }}" required></label><label><span class="mb-2 block text-sm font-bold">Артикул</span><input class="form-field" name="sku" value="{{ old('sku', $product->sku) }}"></label></div>
        <label><span class="mb-2 block text-sm font-bold">Короткое описание</span><textarea class="form-field min-h-24" name="short_description">{{ old('short_description', $product->short_description) }}</textarea></label>
        <label><span class="mb-2 block text-sm font-bold">Полное описание</span><textarea class="form-field min-h-40" name="description">{{ old('description', $product->description) }}</textarea></label>
        <div class="grid gap-5 sm:grid-cols-3"><label><span class="mb-2 block text-sm font-bold">Цена, копейки</span><input class="form-field" type="number" min="0" name="price_minor" value="{{ old('price_minor', $product->price_minor ?? 0) }}" required></label><label><span class="mb-2 block text-sm font-bold">Статус остатка</span><select class="form-field" name="stock_status">@foreach (\App\StockStatus::cases() as $stockStatus)<option value="{{ $stockStatus->value }}" @selected(old('stock_status', $product->stock_status?->value) === $stockStatus->value)>{{ $stockStatus->label() }}</option>@endforeach</select></label><label><span class="mb-2 block text-sm font-bold">Порядок</span><input class="form-field" type="number" min="0" name="position" value="{{ old('position', $product->position ?? 0) }}" required></label></div>
        <fieldset><legend class="mb-3 text-sm font-bold">Категории</legend><div class="grid gap-2 sm:grid-cols-2">@foreach ($categories as $category)<label class="flex gap-3 rounded-xl border border-ink/10 p-3 text-sm"><input class="mt-1 size-4" type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, old('category_ids', $product->categories?->modelKeys() ?? [])))>{{ $category->name }}</label>@endforeach</div></fieldset>
        <div class="grid gap-3 sm:grid-cols-3"><label class="flex gap-3 text-sm"><input class="mt-1 size-4" type="checkbox" name="is_purchasable" value="1" @checked(old('is_purchasable', $product->is_purchasable))>Можно купить</label><label class="flex gap-3 text-sm"><input class="mt-1 size-4" type="checkbox" name="is_published" value="1" @checked(old('is_published', $product->is_published))>Опубликовать</label><label class="flex gap-3 text-sm"><input class="mt-1 size-4" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>Показывать на главной</label></div>
        <label><span class="mb-2 block text-sm font-bold">SEO title</span><input class="form-field" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}"></label>
        <label><span class="mb-2 block text-sm font-bold">SEO description</span><textarea class="form-field min-h-24" name="meta_description">{{ old('meta_description', $product->meta_description) }}</textarea></label>
        <button class="button-primary" type="submit">Сохранить</button>
    </form>
    @if ($editing)
        <form class="mt-5" method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Снять товар с публикации?');">@csrf @method('DELETE')<button class="text-sm font-bold text-red-900 underline underline-offset-4" type="submit">Снять с публикации</button></form>
    @endif
@endsection
