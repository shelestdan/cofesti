<?php

namespace App\Http\Controllers;

use App\Actions\ResolveCart;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\StockStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(ResolveCart $resolveCart): View
    {
        $items = $resolveCart->handle($this->cart());

        return view('cart.index', [
            'items' => $items,
            'totalMinor' => $items->sum('line_total_minor'),
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $product = Product::query()
            ->published()
            ->with('variants')
            ->findOrFail($request->integer('product_id'));

        if (! $product->is_purchasable || $product->stock_status !== StockStatus::Available) {
            throw ValidationException::withMessages([
                'product_id' => 'Этот товар сейчас нельзя добавить в корзину.',
            ]);
        }

        $variant = $this->resolvableVariant($product, $request->integer('product_variant_id'));
        $cartKey = $product->id.':'.($variant?->id ?? 'base');
        $cart = $this->cart();
        $currentQuantity = (int) ($cart[$cartKey]['quantity'] ?? 0);

        $cart[$cartKey] = [
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => min(99, $currentQuantity + $request->integer('quantity')),
        ];
        Session::put('cart', $cart);

        return back()->with('status', 'Товар добавлен в корзину.');
    }

    public function update(UpdateCartItemRequest $request, string $cartKey): RedirectResponse
    {
        $cart = $this->cart();

        abort_unless(isset($cart[$cartKey]), 404);

        $cart[$cartKey]['quantity'] = $request->integer('quantity');
        Session::put('cart', $cart);

        return back()->with('status', 'Количество обновлено.');
    }

    public function destroy(string $cartKey): RedirectResponse
    {
        $cart = $this->cart();

        abort_unless(isset($cart[$cartKey]), 404);

        unset($cart[$cartKey]);
        Session::put('cart', $cart);

        return back()->with('status', 'Товар удалён из корзины.');
    }

    /**
     * @return array<string, array{product_id: int, product_variant_id: int|null, quantity: int}>
     */
    private function cart(): array
    {
        $cart = Session::get('cart', []);

        return is_array($cart) ? $cart : [];
    }

    private function resolvableVariant(Product $product, int $variantId): ?ProductVariant
    {
        if ($variantId === 0 && ! (bool) data_get($product->metadata, 'has_options')) {
            return null;
        }

        $variant = $product->variants->first(
            fn (ProductVariant $candidate): bool => $candidate->id === $variantId
                && $candidate->is_active
                && $candidate->is_purchasable
                && $candidate->price_minor !== null,
        );

        if ($variant === null) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'Выберите вариант с подтверждённой ценой.',
            ]);
        }

        return $variant;
    }
}
