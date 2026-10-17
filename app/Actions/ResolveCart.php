<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ResolveCart
{
    /**
     * @param  array<string, array{product_id: int, product_variant_id: int|null, quantity: int}>  $cart
     * @return Collection<int, array{key: string, product: Product, variant: ProductVariant|null, quantity: int, unit_price_minor: int, line_total_minor: int}>
     */
    public function handle(array $cart, bool $lockProducts = false): Collection
    {
        $productIds = collect($cart)
            ->pluck('product_id')
            ->filter()
            ->map(fn (mixed $productId): int => (int) $productId)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        $products = Product::query()
            ->published()
            ->whereIn('id', $productIds)
            ->with(['variants', 'media'])
            ->when($lockProducts, fn (Builder $query): Builder => $query->lockForUpdate())
            ->get()
            ->keyBy('id');

        return collect($cart)
            ->map(function (array $item, string $key) use ($products): ?array {
                $product = $products->get((int) $item['product_id']);

                if ($product === null || ! $product->is_purchasable) {
                    return null;
                }

                $variant = null;
                if ($item['product_variant_id'] !== null) {
                    $variant = $product->variants->first(
                        fn (ProductVariant $candidate): bool => $candidate->id === (int) $item['product_variant_id']
                            && $candidate->is_active
                            && $candidate->is_purchasable
                            && $candidate->price_minor !== null,
                    );

                    if ($variant === null) {
                        return null;
                    }
                } elseif ((bool) data_get($product->metadata, 'has_options')) {
                    return null;
                }

                $unitPriceMinor = $variant?->price_minor ?? $product->price_minor;
                $quantity = max(1, min(99, (int) $item['quantity']));

                return [
                    'key' => $key,
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unit_price_minor' => $unitPriceMinor,
                    'line_total_minor' => $unitPriceMinor * $quantity,
                ];
            })
            ->filter()
            ->values();
    }
}
