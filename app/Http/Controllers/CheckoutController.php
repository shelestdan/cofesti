<?php

namespace App\Http\Controllers;

use App\Actions\CreateOrder;
use App\Actions\ResolveCart;
use App\Http\Requests\StoreCheckoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function create(ResolveCart $resolveCart): View|RedirectResponse
    {
        $items = $resolveCart->handle($this->cart());

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Корзина пуста или требует обновления.']);
        }

        return view('checkout.create', [
            'items' => $items,
            'totalMinor' => $items->sum('line_total_minor'),
            'idempotencyKey' => Str::uuid()->toString(),
        ]);
    }

    public function store(StoreCheckoutRequest $request, CreateOrder $createOrder): RedirectResponse
    {
        try {
            $order = $createOrder->handle(
                $request->safe()->except('privacy_accepted'),
                $this->cart(),
            );
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['cart' => $exception->getMessage()]);
        }

        Session::forget('cart');
        Session::put('checkout_order_number', $order->number);

        return redirect()->route('checkout.success');
    }

    public function success(): View|RedirectResponse
    {
        $orderNumber = Session::pull('checkout_order_number');

        if ($orderNumber === null) {
            return redirect()->route('home');
        }

        return view('checkout.success', compact('orderNumber'));
    }

    /**
     * @return array<string, array{product_id: int, product_variant_id: int|null, quantity: int}>
     */
    private function cart(): array
    {
        $cart = Session::get('cart', []);

        return is_array($cart) ? $cart : [];
    }
}
