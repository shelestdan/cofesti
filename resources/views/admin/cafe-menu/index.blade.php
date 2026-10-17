@extends('layouts.admin')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-5"><div><p class="eyebrow">Кофейня</p><h1 class="display-title mt-3 text-5xl">Меню</h1></div><a class="button-primary" href="{{ route('admin.cafe-menu-items.create') }}">Добавить позицию</a></div>
    <div class="admin-card mt-8 overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="border-b border-ink/10 text-xs uppercase tracking-[0.14em] text-ink/55"><tr><th class="p-3">Позиция</th><th class="p-3">Категория</th><th class="p-3">Цена</th><th class="p-3">Статус</th><th class="p-3"></th></tr></thead><tbody>@forelse ($items as $item)<tr class="border-b border-ink/10 last:border-0"><td class="p-3 font-bold">{{ $item->name }}</td><td class="p-3">{{ $item->category ?: '—' }}</td><td class="p-3">{{ $item->price_minor === null ? 'Не указана' : number_format($item->price_minor / 100, 0, ',', ' ').' ₽' }}</td><td class="p-3">{{ $item->is_published && $item->is_available ? 'Опубликована' : 'Черновик / недоступна' }}</td><td class="p-3 text-right"><a class="font-bold underline underline-offset-4" href="{{ route('admin.cafe-menu-items.edit', $item) }}">Изменить</a></td></tr>@empty<tr><td class="p-3" colspan="5">Пока нет позиций меню.</td></tr>@endforelse</tbody></table></div>
    <div class="mt-6">{{ $items->links() }}</div>
@endsection
