@extends('layouts.admin')

@section('content')
    @php($editing = $category->exists)
    <p class="eyebrow">Каталог</p>
    <h1 class="display-title mt-3 text-5xl">{{ $editing ? 'Изменить категорию' : 'Новая категория' }}</h1>
    <form class="admin-card mt-8 grid max-w-3xl gap-5" method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <label><span class="mb-2 block text-sm font-bold">Название</span><input class="form-field" name="name" value="{{ old('name', $category->name) }}" required></label>
        <label><span class="mb-2 block text-sm font-bold">Адрес</span><input class="form-field" name="slug" value="{{ old('slug', $category->slug) }}" required></label>
        <label><span class="mb-2 block text-sm font-bold">Родительская категория</span><select class="form-field" name="parent_id"><option value="">Нет</option>@foreach ($parentCategories as $parentCategory)<option value="{{ $parentCategory->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parentCategory->id)>{{ $parentCategory->name }}</option>@endforeach</select></label>
        <label><span class="mb-2 block text-sm font-bold">Описание</span><textarea class="form-field min-h-28" name="description">{{ old('description', $category->description) }}</textarea></label>
        <label><span class="mb-2 block text-sm font-bold">Порядок</span><input class="form-field" type="number" min="0" name="position" value="{{ old('position', $category->position ?? 0) }}" required></label>
        <label class="flex gap-3 text-sm"><input class="mt-1 size-4" type="checkbox" name="is_published" value="1" @checked(old('is_published', $category->is_published))>Опубликовать</label>
        <label><span class="mb-2 block text-sm font-bold">SEO title</span><input class="form-field" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}"></label>
        <label><span class="mb-2 block text-sm font-bold">SEO description</span><textarea class="form-field min-h-24" name="meta_description">{{ old('meta_description', $category->meta_description) }}</textarea></label>
        <button class="button-primary" type="submit">Сохранить</button>
    </form>
@endsection
