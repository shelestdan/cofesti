@extends('layouts.admin')

@section('content')
    <p class="eyebrow">Коммуникации</p><h1 class="display-title mt-3 text-5xl">Обращения</h1>
    <form class="mt-6 flex gap-3" method="GET" action="{{ route('admin.inquiries.index') }}"><select class="form-field max-w-60" name="status"><option value="">Все статусы</option>@foreach (\App\InquiryStatus::cases() as $inquiryStatus)<option value="{{ $inquiryStatus->value }}" @selected($status === $inquiryStatus->value)>{{ $inquiryStatus->label() }}</option>@endforeach</select><button class="button-secondary" type="submit">Фильтровать</button></form>
    <div class="admin-card mt-6 overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="border-b border-ink/10 text-xs uppercase tracking-[0.14em] text-ink/55"><tr><th class="p-3">Тип</th><th class="p-3">Имя</th><th class="p-3">Контакт</th><th class="p-3">Статус</th><th class="p-3"></th></tr></thead><tbody>@foreach ($inquiries as $inquiry)<tr class="border-b border-ink/10 last:border-0"><td class="p-3">{{ $inquiry->type }}</td><td class="p-3 font-bold">{{ $inquiry->name }}</td><td class="p-3">{{ $inquiry->phone ?: $inquiry->email }}</td><td class="p-3">{{ $inquiry->status->label() }}</td><td class="p-3 text-right"><a class="font-bold underline underline-offset-4" href="{{ route('admin.inquiries.show', $inquiry) }}">Открыть</a></td></tr>@endforeach</tbody></table></div>
    <div class="mt-6">{{ $inquiries->links() }}</div>
@endsection
