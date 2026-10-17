@extends('layouts.app')

@section('content')
    <section class="home-hero" aria-labelledby="hero-title">
        <img class="hero-artwork" src="{{ asset('images/prototype/hero-reference-artwork.webp') }}" alt="" width="1435" height="1096" fetchpriority="high">
        <div class="hero-heading">
            <h1 id="hero-title" class="hero-title">Кофе в вашем ритме</h1>
            <p class="hero-subtitle">Разные сценарии. Одна любовь к кофе.</p>
        </div>

        <div class="hero-scenarios">
            <a class="hero-scenario hero-cafe" href="{{ route('cafe.index') }}">
                <img class="mobile-scenario-image" src="{{ asset('images/prototype/cafe-scene-prototype.webp') }}" alt="" width="1122" height="1402">
                <div class="hero-scenario-copy">
                    <h2>Зайти<br>в кофейню</h2>
                    <span class="scenario-action"><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </div>
            </a>
            <a class="hero-scenario hero-takeaway" href="{{ route('cafe.index') }}">
                <img class="mobile-scenario-image" src="{{ asset('images/prototype/takeaway-cup-prototype.webp') }}" alt="" width="1122" height="1402">
                <div class="hero-scenario-copy">
                    <h2>Взять<br>с собой</h2>
                    <span class="scenario-action"><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </div>
            </a>
            <a class="hero-scenario hero-home" href="{{ route('catalog.index') }}">
                <img class="mobile-scenario-image" src="{{ asset('images/prototype/coffee-at-home-prototype.webp') }}" alt="" width="1122" height="1402">
                <div class="hero-scenario-copy">
                    <h2>Выбрать<br>для дома</h2>
                    <span class="scenario-action"><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </div>
            </a>
        </div>

        <div class="hero-loyalty">
            <div>
                <p class="loyalty-title">Лояльность — скоро</p>
                <p class="loyalty-note">Больше встреч.<br>Больше хорошего кофе.</p>
            </div>
            <p class="loyalty-outro">Всё хорошее<br>ещё впереди</p>
        </div>
    </section>

    <div class="home-lower">
    <section class="page-shell section-space featured-section">
        <div class="section-heading featured-heading">
            <div>
                <p class="eyebrow" data-reveal="badge">Для ваших кофейных ритуалов</p>
                <h2 class="display-title section-title" data-reveal="title">Выберите своё</h2>
            </div>
            <div class="featured-actions" data-reveal>
                <a class="editorial-link" href="{{ route('catalog.index') }}">Весь каталог <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                <div class="featured-controls" hidden>
                    <button type="button" class="rail-button" data-rail-prev aria-label="Предыдущие товары" aria-controls="featured-products" disabled><i class="bi bi-arrow-left" aria-hidden="true"></i></button>
                    <button type="button" class="rail-button" data-rail-next aria-label="Следующие товары" aria-controls="featured-products"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>

        @if ($featuredProducts->isNotEmpty())
        <div id="featured-products" class="product-grid featured-rail mt-12" role="region" aria-label="Товары COFESTI" tabindex="0">
            @foreach ($featuredProducts as $product)
                @include('catalog.card', ['product' => $product])
            @endforeach
        </div>
        <div class="featured-progress" data-rail-progress aria-hidden="true">
            <span data-rail-position>01</span>
            <div class="featured-progress-track"><span></span></div>
            <span>{{ str_pad((string) $featuredProducts->count(), 2, '0', STR_PAD_LEFT) }}</span>
        </div>
        @endif
    </section>

    <section id="wholesale" class="wholesale-section section-space">
        <div class="page-shell"><div class="wholesale-panel">
            <img class="wholesale-branch" src="{{ asset('images/prototype/coffee-branch-prototype.webp') }}" alt="" loading="lazy" aria-hidden="true">
            <div class="wholesale-copy">
                <p class="eyebrow" data-reveal="badge">Для бизнеса</p>
                <h2 class="display-title" data-reveal="title">Хороший кофе <span class="title-accent">объединяет бизнес</span></h2>
                <p class="mt-6 max-w-lg text-base leading-7 text-ink/75" data-reveal>Кофе, чай и ингредиенты для вашего проекта. Расскажите, что ищете — обсудим ассортимент и условия поставки.</p>
                <div class="wholesale-visual" data-parallax-frame data-reveal="image">
                <img class="wholesale-image" src="{{ asset('images/prototype/coffee-at-home-prototype.webp') }}" alt="Кофейные зёрна и чайник для заваривания" width="1122" height="1402" loading="lazy">
                <img class="wholesale-cup" data-parallax="30" src="{{ asset('images/prototype/takeaway-cup-cutout.webp') }}" alt="" width="1086" height="1448" loading="lazy">
                </div>
            </div>

            <form class="inquiry-form" data-reveal method="POST" action="{{ route('inquiries.store') }}">
                @csrf
                <input type="hidden" name="type" value="wholesale">
                <div class="grid gap-4">
                    <label>
                        <span class="mb-2 block text-sm font-bold">Имя</span>
                        <input class="form-field" name="name" value="{{ old('name') }}" autocomplete="name" required>
                    </label>
                    <label>
                        <span class="mb-2 block text-sm font-bold">Телефон</span>
                        <input class="form-field" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel">
                    </label>
                    <label>
                        <span class="mb-2 block text-sm font-bold">Email</span>
                        <input class="form-field" type="email" name="email" value="{{ old('email') }}" autocomplete="email">
                    </label>
                    <label>
                        <span class="mb-2 block text-sm font-bold">Что нужно</span>
                        <textarea class="form-field min-h-28" name="message">{{ old('message') }}</textarea>
                    </label>
                    <label class="flex gap-3 text-sm leading-5">
                        <input class="mt-1 size-4" type="checkbox" name="privacy_accepted" value="1" required>
                        <span>Согласен на обработку данных для ответа на запрос. Текст политики добавляется после согласования.</span>
                    </label>
                    <button class="button-primary mt-2" type="submit">Отправить запрос</button>
                </div>
            </form>
        </div></div>
    </section>

    <section id="about" class="page-shell section-space about-section">
        <div class="about-collage">
            <div class="about-image" data-parallax-frame data-reveal="image">
                <img data-parallax="20" src="{{ asset('images/prototype/cafe-scene-prototype.webp') }}" alt="Чашка кофе на столике в солнечной кофейне" width="1122" height="1402" loading="lazy">
            </div>
            <div class="about-inset" data-parallax-frame data-reveal="image" aria-hidden="true">
                <img data-parallax="14" src="{{ asset('images/prototype/takeaway-cup-prototype.webp') }}" alt="" width="1122" height="1402" loading="lazy">
            </div>
        </div>
        <div class="about-copy">
            <p class="eyebrow" data-reveal="badge">Больше, чем кофе</p>
            <h2 class="display-title" data-reveal="title">У каждого дня<br><span class="title-accent">есть свой ритм.</span></h2>
            <p data-reveal>Неспешная встреча в кофейне. Любимый напиток по дороге. Маленький ритуал дома. COFESTI собирает их вместе — вокруг хорошего кофе.</p>
            <a class="editorial-link" data-reveal href="{{ route('cafe.index') }}">Заглянуть в кофейню <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
    </section>
    </div>
@endsection
