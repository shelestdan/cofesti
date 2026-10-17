@extends('layouts.admin')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-5">
        <div><p class="eyebrow">Каталог</p><h1 class="display-title mt-3 text-5xl">Категории</h1></div>
        <a class="button-primary" href="{{ route('admin.categories.create') }}">Добавить категорию</a>
    </div>
    <div class="admin-card mt-8 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-ink/10 text-xs uppercase tracking-[0.14em] text-ink/55"><tr><th class="p-3">Название</th><th class="p-3">Родитель</th><th class="p-3">Товары</th><th class="p-3">Статус</th><th class="p-3"></th></tr></thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr class="border-b border-ink/10 last:border-0"><td class="p-3 font-bold">{{ $category->name }}</td><td class="p-3">{{ $category->parent?->name ?? '—' }}</td><td class="p-3">{{ $category->products_count }}</td><td class="p-3">{{ $category->is_published ? 'Опубликована' : 'Черновик' }}</td><td class="p-3 text-right"><a class="font-bold underline underline-offset-4" href="{{ route('admin.categories.edit', $category) }}">Изменить</a></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
