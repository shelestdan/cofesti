@extends('layouts.app')

@section('content')
    <section class="page-shell business-page">
        <nav class="business-breadcrumbs" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}">Главная</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Для бизнеса</span>
        </nav>

        <div class="business-layout">
            <div class="business-intro" data-reveal>
                <p class="eyebrow">Партнёрам ARMAT</p>
                <h1 class="display-title">Для вашего<br>бизнеса</h1>
                <p class="business-description">Кофе, чай и ингредиенты для вашего проекта. Расскажите, что ищете — обсудим ассортимент и условия поставки.</p>
                <a class="editorial-link business-form-link" href="#partner-form">Заполнить анкету <i class="bi bi-arrow-down-right" aria-hidden="true"></i></a>

                <div id="business-contacts" class="business-contacts">
                    <h2 class="display-title">Прямая связь</h2>
                    <address>
                        @foreach (array_filter(config('business.phones')) as $phone)
                            <a href="tel:{{ preg_replace('/[^+0-9]/', '', $phone) }}">
                                <span>Телефон</span><strong>{{ $phone }}</strong>
                                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                            </a>
                        @endforeach
                        <a href="mailto:{{ config('business.email') }}">
                            <span>Email</span><strong>{{ config('business.email') }}</strong>
                            <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                        </a>
                        <div class="business-address"><span>Адрес</span><p>{{ config('business.address') }}</p></div>
                    </address>
                </div>
            </div>

            <div id="partner-form" class="business-form-panel" data-reveal>
                <p class="eyebrow">Начнём знакомство</p>
                <h2 class="display-title">Анкета партнёра</h2>
                <p class="business-form-note" id="partner-contact-hint">Укажите телефон или email, чтобы мы могли ответить.</p>
                <form class="business-form" method="POST" action="{{ route('inquiries.store') }}" aria-label="Анкета партнёра">
                    @csrf
                    <input type="hidden" name="type" value="wholesale">
                    <label for="partner-name">
                        <span>Как вас зовут</span>
                        <input id="partner-name" class="form-field" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required>
                    </label>
                    <div class="business-contact-fields">
                        <label for="partner-phone">
                            <span>Телефон</span>
                            <input id="partner-phone" class="form-field" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" maxlength="50" aria-describedby="partner-contact-hint">
                        </label>
                        <label for="partner-email">
                            <span>Email</span>
                            <input id="partner-email" class="form-field" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" aria-describedby="partner-contact-hint">
                        </label>
                    </div>
                    <label for="partner-message">
                        <span>О вашем бизнесе и запросе</span>
                        <textarea id="partner-message" class="form-field" name="message" rows="4" maxlength="3000" placeholder="Компания, город, интересующий ассортимент и объём закупки">{{ old('message') }}</textarea>
                    </label>
                    <label class="business-consent" for="partner-privacy">
                        <input id="partner-privacy" type="checkbox" name="privacy_accepted" value="1" @checked(old('privacy_accepted')) required>
                        <span>Согласен на обработку данных для ответа на запрос. Текст политики добавляется после согласования.</span>
                    </label>
                    <button class="button-primary" type="submit">Отправить анкету <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>
                </form>
            </div>
        </div>
    </section>
@endsection
