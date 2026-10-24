<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ isset($title) ? $title.' — Админка ARMAT' : 'Админка ARMAT' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-latte">
    <header class="border-b border-ink/10 bg-cream">
        <div class="page-shell flex min-h-20 flex-wrap items-center justify-between gap-5">
            <a class="font-bold tracking-[0.16em]" href="{{ route('admin.dashboard') }}">ARMAT / АДМИНКА</a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}">На сайт</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="font-bold underline underline-offset-4" type="submit">Выйти</button>
                </form>
            </div>
        </div>
    </header>

    <div class="page-shell grid gap-8 py-8 lg:grid-cols-[13rem_1fr]">
        <aside class="h-fit rounded-3xl bg-ink p-5 text-cream">
            <nav class="flex flex-col gap-1 text-sm" aria-label="Административная навигация">
                <a class="rounded-xl px-3 py-2 hover:bg-cream/10" href="{{ route('admin.dashboard') }}">Обзор</a>
                <a class="rounded-xl px-3 py-2 hover:bg-cream/10" href="{{ route('admin.products.index') }}">Товары</a>
                <a class="rounded-xl px-3 py-2 hover:bg-cream/10" href="{{ route('admin.categories.index') }}">Категории</a>
                <a class="rounded-xl px-3 py-2 hover:bg-cream/10" href="{{ route('admin.cafe-menu-items.index') }}">Меню кофейни</a>
                <a class="rounded-xl px-3 py-2 hover:bg-cream/10" href="{{ route('admin.orders.index') }}">Заказы</a>
                <a class="rounded-xl px-3 py-2 hover:bg-cream/10" href="{{ route('admin.inquiries.index') }}">Обращения</a>
            </nav>
        </aside>

        <main>
            @if (session('status'))
                <div class="mb-6 rounded-2xl bg-olive px-4 py-3 text-sm font-semibold text-cream" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-900/20 bg-red-50 px-4 py-3 text-sm text-red-950" role="alert">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
