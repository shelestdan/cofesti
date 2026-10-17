@extends('layouts.app')

@section('content')
    <section class="page-shell pb-20">
        <div class="page-intro" data-reveal><p class="eyebrow">Корзина</p><h1 class="display-title">Ваш выбор</h1></div>

        @if ($items->isEmpty())
            <div class="empty-state" data-reveal><div>
                <h2>Здесь будет ваш<br>любимый кофе.</h2>
                <p class="mt-5">Корзина пока пуста. Загляните в каталог — возможно, ваш новый любимый вкус уже там.</p>
                <a class="button-primary mt-6" href="{{ route('catalog.index') }}">Перейти в каталог</a></div>
                <img class="empty-state-image" src="{{ asset('images/prototype/takeaway-cup-prototype.webp') }}" alt="Крафтовый стакан кофе в тёплом солнечном свете" loading="lazy">
            </div>
        @else
            <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_22rem]">
                <div class="cart-items divide-y divide-ink/15 border-y border-ink/15" data-reveal>
                    @foreach ($items as $item)
                        @php($media = $item['product']->media->first())
                        <article class="grid gap-5 py-6 sm:grid-cols-[7rem_1fr_auto] sm:items-center">
                            <div class="aspect-square overflow-hidden rounded-2xl bg-latte">
                                @if ($media?->path)
                                    <img class="h-full w-full object-cover" src="{{ asset('storage/'.$media->path) }}" alt="{{ $media->alt_text ?: $item['product']->name }}">
                                @endif
                            </div>
                            <div>
                                <h2 class="font-bold">{{ $item['product']->name }}</h2>
                                @if ($item['variant'])
                                    <p class="mt-1 text-sm text-ink/60">{{ $item['variant']->name ?: collect($item['variant']->attributes)->filter()->join(', ') }}</p>
                                @endif
                                <p class="mt-2 font-bold">{{ number_format($item['unit_price_minor'] / 100, 0, ',', ' ') }} ₽</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                                <form method="POST" action="{{ route('cart.update', $item['key']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="quantity-{{ $loop->index }}">Количество</label>
                                    <input id="quantity-{{ $loop->index }}" class="form-field w-20 bg-cream text-center" type="number" name="quantity" min="1" max="99" value="{{ $item['quantity'] }}">
                                    <button class="mt-2 text-xs font-bold underline underline-offset-4" type="submit">Обновить</button>
                                </form>
                                <form method="POST" action="{{ route('cart.destroy', $item['key']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-bold text-ink/60 underline underline-offset-4" type="submit">Удалить</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="order-summary h-fit bg-ink p-8 text-cream" data-reveal>
                    <p class="eyebrow text-latte">Итого</p>
                    <p class="display-title mt-5 text-5xl">{{ number_format($totalMinor / 100, 0, ',', ' ') }} ₽</p>
                    <p class="mt-5 text-sm leading-6 text-cream/70">Проверьте ваш выбор и переходите к оформлению заказа.</p>
                    <a class="button-secondary mt-7 w-full border-cream text-cream hover:bg-cream hover:text-ink" href="{{ route('checkout.create') }}">Оформить заказ</a>
                </aside>
            </div>
        @endif
    </section>
@endsection
