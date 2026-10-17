@extends('layouts.app')

@section('content')
    <section class="page-shell py-16 sm:py-28">
        <div class="order-summary mx-auto max-w-md bg-latte p-7 sm:p-10" data-reveal>
            <p class="eyebrow">Для команды</p>
            <h1 class="display-title mt-3 text-5xl">Вход в админку</h1>
            <form class="mt-8 grid gap-5" method="POST" action="{{ route('login.store') }}">
                @csrf
                <label>
                    <span class="mb-2 block text-sm font-bold">Email</span>
                    <input class="form-field bg-cream" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </label>
                <label>
                    <span class="mb-2 block text-sm font-bold">Пароль</span>
                    <input class="form-field bg-cream" type="password" name="password" autocomplete="current-password" required>
                </label>
                <label class="flex items-center gap-3 text-sm">
                    <input class="size-4" type="checkbox" name="remember" value="1">
                    Запомнить на этом устройстве
                </label>
                <button class="button-primary" type="submit">Войти</button>
            </form>
        </div>
    </section>
@endsection
