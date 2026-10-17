@extends('layouts.app')

@section('content')
    <section class="page-shell py-12 sm:py-20">
        <div class="cafe-hero">
            <div class="page-intro" data-reveal>
                <p class="eyebrow">Хороший кофе собирает людей</p>
                <h1 class="display-title">Зайти, остаться<br>или взять с собой</h1>
                <p>Для неспешного утра, тёплых встреч и маленькой паузы посреди большого дня.</p>
                <a class="editorial-link mt-5" href="#cafe-menu">Посмотреть меню <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
            </div>
            <img class="cafe-hero-image" src="{{ asset('images/prototype/cafe-scene-prototype.webp') }}" alt="Чашка кофе в солнечной кофейне" width="1122" height="1402" data-reveal>
        </div>

        <div id="cafe-menu" class="section-heading mt-20" data-reveal>
            <div><p class="eyebrow">Пауза со вкусом</p><h2 class="display-title mt-3">Меню кофейни</h2></div>
        </div>

        @if ($menuItems->isEmpty())
            <div class="mt-10 border-y border-ink/20 py-10" data-reveal>
                <p class="display-title text-4xl">Меню скоро появится здесь</p>
                <p class="mt-5 max-w-xl text-base leading-7 text-ink/70">Мы готовим меню напитков и блюд. А пока можно выбрать кофе, чай и ингредиенты для дома в каталоге.</p>
                <a class="button-primary mt-7" href="{{ route('catalog.index') }}">Открыть каталог</a>
            </div>
        @else
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($menuItems as $item)
                    @php($media = $item->media)
                    <article class="cafe-menu-card" data-reveal>
                        @if ($media?->path)
                            <img class="aspect-[4/3] w-full rounded-[1.5rem] object-cover" src="{{ asset('storage/'.$media->path) }}" alt="{{ $media->alt_text ?: $item->name }}">
                        @endif
                        <p class="mt-5 text-xs uppercase tracking-[0.18em] text-ink/55">{{ $item->category }}</p>
                        <h2 class="display-title mt-2 text-4xl">{{ $item->name }}</h2>
                        @if ($item->description)
                            <p class="mt-3 text-sm leading-6 text-ink/70">{{ $item->description }}</p>
                        @endif
                        @if ($item->price_minor !== null)
                            <p class="mt-5 font-bold">{{ number_format($item->price_minor / 100, 0, ',', ' ') }} ₽</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
