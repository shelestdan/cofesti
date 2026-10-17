@extends('layouts.app')

@section('content')
    <section class="page-shell py-20 sm:py-32">
        <div class="order-summary max-w-2xl bg-olive p-8 text-cream sm:p-14" data-reveal>
            <p class="eyebrow text-latte">Заказ сохранён</p>
            <h1 class="display-title mt-5 text-6xl leading-none">Спасибо</h1>
            <p class="mt-6 text-base leading-7 text-cream/75">Номер заказа: <span class="font-bold text-cream">{{ $orderNumber }}</span>. Сохраните его для дальнейшего обращения.</p>
            <a class="button-secondary mt-9 border-cream text-cream hover:bg-cream hover:text-ink" href="{{ route('home') }}">На главную</a>
        </div>
    </section>
@endsection
