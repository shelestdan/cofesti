@extends('layouts.app')

@section('content')
    @php($media = $product->media->first())
    <section class="page-shell py-12 sm:py-20">
        <nav class="text-sm text-ink/60" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}">Главная</a>
            <span aria-hidden="true"> / </span>
            <a href="{{ route('catalog.index') }}">Каталог</a>
            @if ($product->categories->isNotEmpty())
                <span aria-hidden="true"> / </span>
                <a href="{{ route('catalog.index', ['category' => $product->categories->first()->slug]) }}">{{ $product->categories->first()->name }}</a>
            @endif
        </nav>

        <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_0.9fr] lg:gap-16">
            <div class="aspect-square overflow-hidden product-detail-image" data-reveal>
                @if ($media?->path)
                    <img class="h-full w-full object-cover" src="{{ asset('storage/'.$media->path) }}" alt="{{ $media->alt_text ?: $product->name }}">
                @endif
            </div>

            <div class="product-details" data-reveal>
                <p class="eyebrow">{{ $product->categories->pluck('name')->join(' · ') }}</p>
                <h1 class="display-title mt-4 text-6xl leading-[0.85] sm:text-7xl">{{ $product->name }}</h1>
                <p class="mt-6 text-2xl font-bold">@if ($product->price_minor > 0){{ number_format($product->price_minor / 100, 0, ',', ' ') }} ₽@else Цена уточняется @endif</p>

                @if ($product->short_description)
                    <p class="mt-6 text-base leading-7 text-ink/75">{{ $product->short_description }}</p>
                @endif

                <form class="mt-8 product-purchase-form" method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if ($product->variants->isNotEmpty())
                        <label>
                            <span class="mb-2 block text-sm font-bold">Вариант</span>
                            <select class="form-field bg-cream" name="product_variant_id">
                                <option value="">Выберите вариант</option>
                                @foreach ($product->variants as $variant)
                                    <option value="{{ $variant->id }}" @disabled(! $variant->is_purchasable || $variant->price_minor === null)>
                                        {{ $variant->name ?: collect($variant->attributes)->filter()->join(', ') }}@if ($variant->price_minor !== null) — {{ number_format($variant->price_minor / 100, 0, ',', ' ') }} ₽ @else — цена уточняется @endif
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <div class="mt-4 flex flex-col gap-4 sm:flex-row">
                        <label class="grow">
                            <span class="mb-2 block text-sm font-bold">Количество</span>
                            <input class="form-field bg-cream" name="quantity" type="number" min="1" max="99" value="1">
                        </label>
                        <button class="button-primary mt-auto {{ $product->is_purchasable && $product->stock_status === \App\StockStatus::Available && $product->variants->where('is_purchasable', true)->isEmpty() && ! data_get($product->metadata, 'has_options') ? '' : 'opacity-50' }}" type="submit" @disabled(! $product->is_purchasable || $product->stock_status !== \App\StockStatus::Available || (data_get($product->metadata, 'has_options') && $product->variants->where('is_purchasable', true)->isEmpty()))>
                            Добавить в корзину
                        </button>
                    </div>
                    @if (data_get($product->metadata, 'has_options') && $product->variants->where('is_purchasable', true)->isEmpty())
                        <p class="mt-4 text-sm leading-6 text-ink/70">Цены вариантов уточняются. Этот товар пока недоступен для заказа.</p>
                    @elseif ($product->stock_status === \App\StockStatus::Unknown)
                        <p class="mt-4 text-sm leading-6 text-ink/70">Доступность не подтверждена, поэтому товар пока нельзя оформить.</p>
                    @endif
                </form>

                @if ($product->description)
                    <div class="mt-10 border-t border-ink/15 pt-7">
                        <h2 class="display-title text-4xl">Описание</h2>
                        <p class="mt-4 whitespace-pre-line text-sm leading-7 text-ink/75">{{ $product->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="bg-latte py-16 sm:py-20">
            <div class="page-shell">
                <h2 class="display-title text-5xl">Ещё в каталоге</h2>
                <div class="product-grid catalog-products mt-10">
                    @foreach ($relatedProducts as $relatedProduct)
                        @include('catalog.card', ['product' => $relatedProduct])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
