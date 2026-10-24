@php($media = $product->media->first())
<article class="product-card" data-reveal>
    <a class="product-image" href="{{ route('catalog.show', $product) }}" aria-label="{{ $product->name }}">
        @if ($media?->path)
            <img @class(['product-photo' => data_get($media->metadata, 'presentation') === 'photograph']) src="{{ asset('storage/'.$media->path) }}" alt="{{ $media->alt_text ?: $product->name }}" loading="lazy">
        @else
            <span class="product-no-photo"><i class="bi bi-image" aria-hidden="true"></i><span>Фотография скоро</span></span>
        @endif
        <span class="product-image-arrow"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
    </a>
    <div class="product-info">
        <p class="product-category">{{ $product->categories->pluck('name')->join(' · ') }}</p>
        <h3><a href="{{ route('catalog.show', $product) }}">{{ $product->name }}</a></h3>
        <div class="product-bottom">
            <span>@if ($product->price_minor > 0){{ number_format($product->price_minor / 100, 0, ',', ' ') }} ₽@else Цена уточняется @endif</span>
            <a href="{{ route('catalog.show', $product) }}">Подробнее <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</article>
