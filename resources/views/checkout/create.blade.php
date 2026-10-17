@extends('layouts.app')

@section('content')
    <section class="page-shell py-12 sm:py-20">
        <div class="grid gap-12 lg:grid-cols-[1fr_0.85fr]">
            <div data-reveal>
                <p class="eyebrow">Оформление заказа</p>
                <h1 class="display-title mt-3 text-6xl leading-none sm:text-7xl">Оставьте контакты</h1>
                <p class="mt-6 max-w-xl text-base leading-7 text-ink/70">Оставьте контактные данные для вашего заказа. Оплата и получение согласовываются отдельно.</p>

                <form class="mt-10 grid gap-5" method="POST" action="{{ route('checkout.store') }}">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
                    <label>
                        <span class="mb-2 block text-sm font-bold">Имя</span>
                        <input class="form-field" name="customer_name" value="{{ old('customer_name') }}" autocomplete="name" required>
                    </label>
                    <label>
                        <span class="mb-2 block text-sm font-bold">Телефон</span>
                        <input class="form-field" type="tel" name="customer_phone" value="{{ old('customer_phone') }}" autocomplete="tel" required>
                    </label>
                    <label>
                        <span class="mb-2 block text-sm font-bold">Email</span>
                        <input class="form-field" type="email" name="customer_email" value="{{ old('customer_email') }}" autocomplete="email">
                    </label>
                    <label>
                        <span class="mb-2 block text-sm font-bold">Комментарий</span>
                        <textarea class="form-field min-h-28" name="customer_comment">{{ old('customer_comment') }}</textarea>
                    </label>
                    <label class="flex gap-3 text-sm leading-5">
                        <input class="mt-1 size-4" type="checkbox" name="privacy_accepted" value="1" required>
                        <span>Согласен на обработку данных для оформления заказа. Текст политики добавляется после согласования.</span>
                    </label>
                    <button class="button-primary" type="submit">Сохранить заказ</button>
                </form>
            </div>

            <aside class="order-summary h-fit bg-latte p-6 sm:p-8" data-reveal>
                <p class="eyebrow">Состав заказа</p>
                <div class="mt-6 divide-y divide-ink/15">
                    @foreach ($items as $item)
                        <div class="flex justify-between gap-4 py-4 text-sm">
                            <div>
                                <p class="font-bold">{{ $item['product']->name }}</p>
                                <p class="mt-1 text-ink/60">{{ $item['quantity'] }} шт.</p>
                            </div>
                            <p class="font-bold">{{ number_format($item['line_total_minor'] / 100, 0, ',', ' ') }} ₽</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 flex justify-between border-t border-ink/20 pt-6 text-lg font-bold">
                    <span>Итого</span>
                    <span>{{ number_format($totalMinor / 100, 0, ',', ' ') }} ₽</span>
                </div>
            </aside>
        </div>
    </section>
@endsection
