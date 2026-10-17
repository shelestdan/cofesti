@extends('layouts.app')

@section('content')
    <section class="page-shell pb-20">
        <div class="page-intro" data-reveal>
            <p class="eyebrow">Ваш маленький ритуал</p>
            <h1 class="display-title">Выберите для себя</h1>
            <p>Кофе, чай и всё, что делает любимый напиток вашим.</p>
        </div>

        <form class="catalog-toolbar" method="GET" action="{{ route('catalog.index') }}">
            <label class="sr-only" for="catalog-search">Поиск</label>
            <input id="catalog-search" class="form-field bg-cream" name="q" value="{{ $queryText }}" placeholder="Найти в каталоге">
            <select class="form-field bg-cream" name="category" aria-label="Категория">
                <option value="">Все категории</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected($selectedCategory === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select class="form-field bg-cream" name="sort" aria-label="Сортировка">
                <option value="name" @selected($sort === '' || $sort === 'name')>По названию</option>
                <option value="price_asc" @selected($sort === 'price_asc')>Сначала дешевле</option>
                <option value="price_desc" @selected($sort === 'price_desc')>Сначала дороже</option>
            </select>
            <button class="button-primary" type="submit">Показать товары</button>
        </form>

        @if ($products->isEmpty())
            <div class="mt-12 empty-state" data-reveal><div>
                <h2 class="display-title text-4xl">Ничего не найдено</h2>
                <p class="mt-3 text-ink/70">Попробуйте изменить поиск или сбросить фильтры.</p>
                <a class="button-secondary mt-6" href="{{ route('catalog.index') }}">Сбросить фильтры</a>
                </div>
                <img class="empty-state-image" src="{{ asset('images/prototype/coffee-at-home-prototype.webp') }}" alt="Кофейные зёрна на светлом столе" loading="lazy">
            </div>
        @else
            <div class="product-grid catalog-products mt-12">
                @foreach ($products as $product)
                    @include('catalog.card', ['product' => $product])
                @endforeach
            </div>

            <div class="mt-12">
                {{ $products->links() }}
            </div>
        @endif
    </section>
@endsection
