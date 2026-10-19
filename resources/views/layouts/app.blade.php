<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $metaDescription ?? 'COFESTI — кофейня, каталог кофе и ингредиентов для напитков.' }}">
    <title>{{ isset($title) ? $title.' — COFESTI' : 'COFESTI' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront {{ request()->routeIs('home') ? 'is-home' : 'is-inner' }}">
    <a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:left-5 focus:top-5 focus:z-50 focus:rounded-full focus:bg-olive focus:px-4 focus:py-2 focus:text-cream">Перейти к содержимому</a>

    <header x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false" class="site-header">
        <div class="header-shell">
            <a href="{{ route('home') }}" class="brand" aria-label="ARMAT, главная">
                <img class="brand-logo" src="{{ asset('images/brand/armat-logo.svg') }}" alt="ARMAT" width="1154" height="256">
                <span class="brand-tagline">Больше<br>чем кофе</span>
            </a>

            <nav class="desktop-nav" aria-label="Основная навигация">
                <a class="hover:text-caramel" href="{{ route('cafe.index') }}">Кофейня</a>
                <a class="hover:text-caramel" href="{{ route('catalog.index') }}">Каталог</a>
                <a class="hover:text-caramel" href="{{ route('home') }}#wholesale">Опт</a>
                <a class="hover:text-caramel" href="{{ route('home') }}#about">О компании</a>
                <a class="hover:text-caramel" href="{{ route('home') }}#contacts">Контакты</a>
            </nav>

            <div class="header-actions">
                <a class="header-icon search-link" href="{{ route('catalog.index') }}#catalog-search" aria-label="Поиск"><i class="bi bi-search" aria-hidden="true"></i></a>
                <span class="header-divider" aria-hidden="true"></span>
                <a class="header-icon cart-link" href="{{ route('cart.index') }}" aria-label="Корзина: {{ count(session('cart', [])) }}"><i class="bi bi-cart3" aria-hidden="true"></i><span class="cart-count">{{ count(session('cart', [])) }}</span></a>
                <button type="button" class="menu-toggle" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen.toString()" aria-controls="mobile-menu" :aria-label="menuOpen ? 'Закрыть меню' : 'Открыть меню'"><i class="bi" :class="menuOpen ? 'bi-x-lg' : 'bi-list'" aria-hidden="true"></i></button>
            </div>
        </div>

        <nav x-cloak x-show="menuOpen" x-transition.opacity.duration.200ms id="mobile-menu" class="mobile-nav" aria-label="Мобильная навигация">
            <div class="page-shell flex flex-col gap-4 px-0 text-lg font-semibold">
                <a @click="menuOpen = false" href="{{ route('cafe.index') }}">Кофейня</a>
                <a @click="menuOpen = false" href="{{ route('catalog.index') }}">Каталог</a>
                <a @click="menuOpen = false" href="{{ route('home') }}#wholesale">Опт</a>
                <a @click="menuOpen = false" href="{{ route('home') }}#about">О компании</a>
                <a @click="menuOpen = false" href="{{ route('home') }}#contacts">Контакты</a>
            </div>
        </nav>
    </header>

    @if (session('status'))
        <div class="page-shell pt-5">
            <div class="rounded-2xl bg-olive px-4 py-3 text-sm font-semibold text-cream" role="status">{{ session('status') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="page-shell pt-5">
            <div class="rounded-2xl border border-red-900/20 bg-red-50 px-4 py-3 text-sm text-red-950" role="alert">
                <p class="font-bold">Проверьте заполнение формы.</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main id="content">
        @yield('content')
    </main>

    <footer id="contacts" class="site-footer">
        <img class="footer-branch" src="{{ asset('images/prototype/coffee-branch-prototype.webp') }}" alt="" aria-hidden="true" loading="lazy">
        <div class="page-shell footer-heading">
            <p class="eyebrow text-latte" data-reveal="badge">Остаёмся на связи</p>
            <p class="display-title" data-reveal="title">Больше, чем кофе.</p>
            <a class="round-link" data-reveal href="{{ route('catalog.index') }}" aria-label="Перейти в каталог"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
        <div class="page-shell grid gap-10 md:grid-cols-[1.3fr_1fr_1fr] footer-columns">
            <div data-reveal>
                <img class="footer-logo" src="{{ asset('images/brand/armat-logo.svg') }}" alt="ARMAT" width="1154" height="256" loading="lazy">
                <p class="mt-4 max-w-sm text-sm leading-6 text-cream/75">Кофейня для встреч. Кофе по дороге. Всё для ваших ритуалов дома. Контактная информация скоро появится здесь.</p>
            </div>
            <div data-reveal>
                <p class="eyebrow text-latte">Навигация</p>
                <div class="mt-4 flex flex-col gap-3 text-sm">
                    <a href="{{ route('cafe.index') }}">Кофейня</a>
                    <a href="{{ route('catalog.index') }}">Каталог</a>
                    <a href="{{ route('home') }}#wholesale">Оптовым клиентам</a>
                </div>
            </div>
            <div data-reveal>
                <p class="eyebrow text-latte">Сервис</p>
                <p class="mt-4 text-sm leading-6 text-cream/75">Программа лояльности — скоро. Больше встреч. Больше хорошего кофе.</p>
                @auth
                    <a class="mt-4 inline-block text-sm font-bold underline underline-offset-4" href="{{ route('admin.dashboard') }}">В админку</a>
                @else
                    <a class="mt-4 inline-block text-sm font-bold underline underline-offset-4" href="{{ route('login') }}">Вход для команды</a>
                @endauth
            </div>
        </div>
        <div class="page-shell footer-bottom"><span>© {{ date('Y') }} COFESTI</span><span>Разные сценарии. Одна любовь к кофе.</span><a href="#content">Наверх <i class="bi bi-arrow-up" aria-hidden="true"></i></a></div>
    </footer>
</body>
</html>
