@extends('layouts.admin')

@section('content')
    @php($editing = $cafeMenuItem->exists)
    <p class="eyebrow">Кофейня</p><h1 class="display-title mt-3 text-5xl">{{ $editing ? 'Изменить позицию' : 'Новая позиция' }}</h1>
    <form class="admin-card mt-8 grid max-w-3xl gap-5" method="POST" action="{{ $editing ? route('admin.cafe-menu-items.update', $cafeMenuItem) : route('admin.cafe-menu-items.store') }}">
        @csrf @if ($editing) @method('PUT') @endif
        <label><span class="mb-2 block text-sm font-bold">Название</span><input class="form-field" name="name" value="{{ old('name', $cafeMenuItem->name) }}" required></label>
        <div class="grid gap-5 sm:grid-cols-2"><label><span class="mb-2 block text-sm font-bold">Адрес</span><input class="form-field" name="slug" value="{{ old('slug', $cafeMenuItem->slug) }}" required></label><label><span class="mb-2 block text-sm font-bold">Категория</span><input class="form-field" name="category" value="{{ old('category', $cafeMenuItem->category) }}"></label></div>
        <label><span class="mb-2 block text-sm font-bold">Описание</span><textarea class="form-field min-h-28" name="description">{{ old('description', $cafeMenuItem->description) }}</textarea></label>
        <div class="grid gap-5 sm:grid-cols-3"><label><span class="mb-2 block text-sm font-bold">Цена, копейки</span><input class="form-field" type="number" min="0" name="price_minor" value="{{ old('price_minor', $cafeMenuItem->price_minor) }}"></label><label><span class="mb-2 block text-sm font-bold">Порядок</span><input class="form-field" type="number" min="0" name="position" value="{{ old('position', $cafeMenuItem->position ?? 0) }}" required></label><label><span class="mb-2 block text-sm font-bold">Фото</span><select class="form-field" name="media_id"><option value="">Не выбрано</option>@foreach ($media as $mediaItem)<option value="{{ $mediaItem->id }}" @selected((string) old('media_id', $cafeMenuItem->media_id) === (string) $mediaItem->id)>{{ $mediaItem->filename ?: 'Файл #'.$mediaItem->id }}</option>@endforeach</select></label></div>
        <div class="flex flex-wrap gap-6"><label class="flex gap-3 text-sm"><input class="mt-1 size-4" type="checkbox" name="is_available" value="1" @checked(old('is_available', $cafeMenuItem->is_available))>Доступна</label><label class="flex gap-3 text-sm"><input class="mt-1 size-4" type="checkbox" name="is_published" value="1" @checked(old('is_published', $cafeMenuItem->is_published))>Опубликовать</label></div>
        <button class="button-primary" type="submit">Сохранить</button>
    </form>
    @if ($editing)<form class="mt-5" method="POST" action="{{ route('admin.cafe-menu-items.destroy', $cafeMenuItem) }}" onsubmit="return confirm('Снять позицию с публикации?');">@csrf @method('DELETE')<button class="text-sm font-bold text-red-900 underline underline-offset-4" type="submit">Снять с публикации</button></form>@endif
@endsection
