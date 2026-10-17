<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInquiryRequest;
use App\InquiryStatus;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $inquiries = Inquiry::query()
            ->when(InquiryStatus::tryFrom($status) !== null, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.inquiries.index', compact('inquiries', 'status'));
    }

    public function show(Inquiry $inquiry): View
    {
        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function update(UpdateInquiryRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $attributes = $request->validated();

        if ($attributes['status'] !== InquiryStatus::New->value && $inquiry->processed_at === null) {
            $attributes['processed_at'] = now();
        }

        $inquiry->update($attributes);

        return redirect()->route('admin.inquiries.show', $inquiry)->with('status', 'Обращение обновлено.');
    }
}
