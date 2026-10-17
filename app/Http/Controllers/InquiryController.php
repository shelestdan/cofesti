<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInquiryRequest;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;

class InquiryController extends Controller
{
    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        Inquiry::query()->create($request->safe()->except('privacy_accepted'));

        return back()->with('status', 'Обращение сохранено. Мы свяжемся после обработки заявки.');
    }
}
