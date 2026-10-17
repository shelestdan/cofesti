@extends('layouts.admin')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-5">
        <div><p class="eyebrow">Каталог</p><h1 class="display-title mt-3 text-5xl">Товары</h1></div>
        <a class="button-primary" href="{{ route('admin.products.create') }}">Добавить товар</a>
    </div>
    <form class="mt-6 flex gap-3" method="GET" action="{{ route('admin.products.index') }}"><label class="sr-only" for="product-query">Поиск</label><input id="product-query" class="form-field" name="q" value="{{ $queryText }}" placeholder="Найти товар"><button class="button-secondary" type="submit">Найти</button></form>
    <div class="admin-card mt-6 overflow-x-auto">
        <table class="min-w-full text-left text-sm"><thead class="border-b border-ink/10 text-xs uppercase tracking-[0.14em] text-ink/55"><tr><th class="p-3">Товар</th><th class="p-3">Категории</th><th class="p-3">Цена</th><th class="p-3">Статус</th><th class="p-3"></th></tr></thead><tbody>
            @foreach ($products as $product)
                <tr class="border-b border-ink/10 last:border-0"><td class="p-3"><p class="font-bold">{{ $product->name }}</p><p class="mt-1 text-xs text-ink/55">{{ $product->sku ?: 'Артикул не указан' }}</p></td><td class="p-3">{{ $product->categories->pluck('name')->join(', ') }}</td><td class="p-3">{{ number_format($product->price_minor / 100, 0, ',', ' ') }} ₽</td><td class="p-3">{{ $product->is_published ? 'Опубликован' : 'Снят' }}</td><td class="p-3 text-right"><a class="font-bold underline underline-offset-4" href="{{ route('admin.products.edit', $product) }}">Изменить</a></td></tr>
            @endforeach
        </tbody></table>
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
@endsection
