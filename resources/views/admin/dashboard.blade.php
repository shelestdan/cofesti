@extends('layouts.admin')

@section('content')
    <p class="eyebrow">Обзор</p>
    <h1 class="display-title mt-3 text-5xl">Рабочее пространство</h1>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a class="admin-card" href="{{ route('admin.products.index') }}"><p class="eyebrow">Товары</p><p class="display-title mt-4 text-5xl">{{ $productCount }}</p></a>
        <a class="admin-card" href="{{ route('admin.orders.index') }}"><p class="eyebrow">Заказы</p><p class="display-title mt-4 text-5xl">{{ $orderCount }}</p></a>
        <a class="admin-card" href="{{ route('admin.inquiries.index') }}"><p class="eyebrow">Обращения</p><p class="display-title mt-4 text-5xl">{{ $inquiryCount }}</p></a>
        <a class="admin-card" href="{{ route('admin.cafe-menu-items.index') }}"><p class="eyebrow">Меню кофейни</p><p class="display-title mt-4 text-5xl">{{ $cafeMenuItemCount }}</p></a>
    </div>
    <div class="admin-card mt-8">
        <h2 class="font-bold">Границы ролей</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-ink/70">Менеджер работает с каталогом, меню, заказами и обращениями. Управление администраторами и техническими секретами не доступно из этого интерфейса.</p>
    </div>
@endsection
