@extends('layouts.admin')

@section('content')
    <p class="eyebrow">Продажи</p><h1 class="display-title mt-3 text-5xl">Заказы</h1>
    <form class="mt-6 flex gap-3" method="GET" action="{{ route('admin.orders.index') }}"><select class="form-field max-w-60" name="status"><option value="">Все статусы</option>@foreach (\App\OrderStatus::cases() as $orderStatus)<option value="{{ $orderStatus->value }}" @selected($status === $orderStatus->value)>{{ $orderStatus->label() }}</option>@endforeach</select><button class="button-secondary" type="submit">Фильтровать</button></form>
    <div class="admin-card mt-6 overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="border-b border-ink/10 text-xs uppercase tracking-[0.14em] text-ink/55"><tr><th class="p-3">Номер</th><th class="p-3">Клиент</th><th class="p-3">Сумма</th><th class="p-3">Статус</th><th class="p-3"></th></tr></thead><tbody>@foreach ($orders as $order)<tr class="border-b border-ink/10 last:border-0"><td class="p-3 font-bold">{{ $order->number }}</td><td class="p-3">{{ $order->customer_name }}<br><span class="text-xs text-ink/55">{{ $order->customer_phone }}</span></td><td class="p-3">{{ number_format($order->total_minor / 100, 0, ',', ' ') }} ₽</td><td class="p-3">{{ $order->status->label() }}</td><td class="p-3 text-right"><a class="font-bold underline underline-offset-4" href="{{ route('admin.orders.show', $order) }}">Открыть</a></td></tr>@endforeach</tbody></table></div>
    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
