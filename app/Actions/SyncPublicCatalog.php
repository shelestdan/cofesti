<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductVariant;
use App\StockStatus;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SyncPublicCatalog
{
    /**
     * @return array{categories: int, products: int, variants: int, media: int, errors: list<string>}
     */
    public function handle(bool $downloadMedia, int $pageSize, int $sleepMilliseconds, ?int $limit = null): array
    {
        $report = [
            'categories' => 0,
            'products' => 0,
            'variants' => 0,
            'media' => 0,
            'errors' => [],
        ];

        $categoriesBySourceId = $this->syncCategories($report);
        $page = 1;
        $totalPages = 1;
        $processedProducts = 0;

        do {
            $response = $this->request('/products', [
                'page' => $page,
                'per_page' => $pageSize,
            ]);
            $totalPages = max(1, (int) $response->header('X-WP-TotalPages', '1'));
            $sourceProducts = $response->json();

            if (! is_array($sourceProducts)) {
                throw new \UnexpectedValueException('Публичный API вернул некорректный список товаров.');
            }

            foreach ($sourceProducts as $sourceProduct) {
                if (! is_array($sourceProduct)) {
                    continue;
                }

                if ($limit !== null && $processedProducts >= $limit) {
                    break 2;
                }

                $processedProducts++;

                try {
                    $this->syncProduct(
                        $sourceProduct,
                        $categoriesBySourceId,
                        $downloadMedia,
                        $sleepMilliseconds,
                        $report,
                    );
                } catch (Throwable $exception) {
                    $report['errors'][] = sprintf(
                        'Товар %s: %s',
                        (string) ($sourceProduct['id'] ?? 'без идентификатора'),
                        $exception->getMessage(),
                    );
                }
            }

            $page++;

            if ($page <= $totalPages) {
                $this->pause($sleepMilliseconds);
            }
        } while ($page <= $totalPages);

        return $report;
    }

    /**
     * @param  array{categories: int, products: int, variants: int, media: int, errors: list<string>}  $report
     * @return array<int, Category>
     */
    private function syncCategories(array &$report): array
    {
        $sourceCategories = $this->request('/products/categories', ['per_page' => 100])->json();

        if (! is_array($sourceCategories)) {
            throw new \UnexpectedValueException('Публичный API вернул некорректный список категорий.');
        }

        $categoriesBySourceId = [];

        foreach ($sourceCategories as $position => $sourceCategory) {
            if (! is_array($sourceCategory) || ! isset($sourceCategory['id'], $sourceCategory['name'], $sourceCategory['slug'])) {
                continue;
            }

            $category = Category::query()->updateOrCreate(
                ['source_id' => (int) $sourceCategory['id']],
                [
                    'source_url' => $sourceCategory['link'] ?? null,
                    'name' => $this->cleanText((string) $sourceCategory['name']) ?? 'Без названия',
                    'slug' => (string) $sourceCategory['slug'],
                    'position' => $position,
                    'is_published' => true,
                ],
            );

            $categoriesBySourceId[(int) $sourceCategory['id']] = $category;
            $report['categories']++;
        }

        foreach ($sourceCategories as $sourceCategory) {
            if (! is_array($sourceCategory) || ! isset($sourceCategory['id'])) {
                continue;
            }

            $category = $categoriesBySourceId[(int) $sourceCategory['id']] ?? null;
            $parent = $categoriesBySourceId[(int) ($sourceCategory['parent'] ?? 0)] ?? null;

            if ($category !== null && $category->parent_id !== $parent?->id) {
                $category->update(['parent_id' => $parent?->id]);
            }
        }

        return $categoriesBySourceId;
    }

    /**
     * @param  array<string, mixed>  $sourceProduct
     * @param  array<int, Category>  $categoriesBySourceId
     * @param  array{categories: int, products: int, variants: int, media: int, errors: list<string>}  $report
     */
    private function syncProduct(
        array $sourceProduct,
        array $categoriesBySourceId,
        bool $downloadMedia,
        int $sleepMilliseconds,
        array &$report,
    ): void {
        if (! isset($sourceProduct['id'], $sourceProduct['name'], $sourceProduct['permalink'])) {
            throw new \UnexpectedValueException('У товара отсутствуют обязательные поля.');
        }

        $product = DB::transaction(function () use ($sourceProduct, $categoriesBySourceId, &$report): Product {
            $product = Product::query()->updateOrCreate(
                ['source_id' => (int) $sourceProduct['id']],
                [
                    'source_url' => $sourceProduct['permalink'] ?? null,
                    'name' => $this->cleanText((string) $sourceProduct['name']) ?? 'Без названия',
                    'slug' => $this->slugFromPermalink((string) $sourceProduct['permalink']),
                    'sku' => $this->nullableString($sourceProduct['sku'] ?? null),
                    'type' => $this->nullableString($sourceProduct['type'] ?? null) ?? 'simple',
                    'short_description' => $this->cleanText($sourceProduct['short_description'] ?? null),
                    'description' => $this->cleanText($sourceProduct['description'] ?? null),
                    'price_minor' => (int) data_get($sourceProduct, 'prices.price', 0),
                    'currency' => (string) data_get($sourceProduct, 'prices.currency_code', 'RUB'),
                    'stock_status' => $this->stockStatus($sourceProduct),
                    'is_purchasable' => (bool) ($sourceProduct['is_purchasable'] ?? false),
                    'is_published' => true,
                    'metadata' => [
                        'attributes' => $this->normalizeAttributes($sourceProduct['attributes'] ?? []),
                        'has_options' => (bool) ($sourceProduct['has_options'] ?? false),
                        'price_range' => data_get($sourceProduct, 'prices.price_range'),
                    ],
                ],
            );

            $categoryIds = [];
            foreach ($sourceProduct['categories'] ?? [] as $sourceCategory) {
                if (is_array($sourceCategory) && isset($sourceCategory['id'])) {
                    $category = $categoriesBySourceId[(int) $sourceCategory['id']] ?? null;

                    if ($category !== null) {
                        $categoryIds[] = $category->id;
                    }
                }
            }

            if ($categoryIds !== []) {
                $product->categories()->syncWithoutDetaching($categoryIds);
            }

            $this->syncVariants($product, $sourceProduct['variations'] ?? [], $report);

            return $product;
        });

        $this->syncMedia($product, $sourceProduct['images'] ?? [], $downloadMedia, $sleepMilliseconds, $report);
        $report['products']++;
    }

    /**
     * @param  list<mixed>  $sourceVariants
     * @param  array{categories: int, products: int, variants: int, media: int, errors: list<string>}  $report
     */
    private function syncVariants(Product $product, array $sourceVariants, array &$report): void
    {
        foreach ($sourceVariants as $position => $sourceVariant) {
            if (! is_array($sourceVariant) || ! isset($sourceVariant['id'])) {
                continue;
            }

            ProductVariant::query()->updateOrCreate(
                ['source_id' => (int) $sourceVariant['id']],
                [
                    'product_id' => $product->id,
                    'name' => $this->variantName($sourceVariant['attributes'] ?? []),
                    'attributes' => $this->normalizeVariantAttributes($sourceVariant['attributes'] ?? []),
                    'stock_status' => StockStatus::Unknown,
                    'is_purchasable' => false,
                    'is_active' => true,
                    'position' => $position,
                ],
            );

            $report['variants']++;
        }
    }

    /**
     * @param  list<mixed>  $sourceImages
     * @param  array{categories: int, products: int, variants: int, media: int, errors: list<string>}  $report
     */
    private function syncMedia(
        Product $product,
        array $sourceImages,
        bool $downloadMedia,
        int $sleepMilliseconds,
        array &$report,
    ): void {
        $attachments = [];

        foreach ($sourceImages as $position => $sourceImage) {
            if (! is_array($sourceImage) || ! isset($sourceImage['src'])) {
                continue;
            }

            try {
                $media = $this->syncMediaRecord($sourceImage, $downloadMedia, $sleepMilliseconds);
                $attachments[$media->id] = [
                    'position' => $position,
                    'is_primary' => $position === 0,
                ];
                $report['media']++;
            } catch (Throwable $exception) {
                $report['errors'][] = sprintf(
                    'Изображение %s: %s',
                    (string) ($sourceImage['id'] ?? $sourceImage['src']),
                    $exception->getMessage(),
                );
            }
        }

        if ($attachments !== []) {
            $product->media()->syncWithoutDetaching($attachments);
        }
    }

    /**
     * @param  array<string, mixed>  $sourceImage
     */
    private function syncMediaRecord(array $sourceImage, bool $downloadMedia, int $sleepMilliseconds): Media
    {
        $sourceUrl = (string) $sourceImage['src'];
        $sourceId = isset($sourceImage['id']) ? (int) $sourceImage['id'] : null;
        $identity = $sourceId !== null ? ['source_id' => $sourceId] : ['source_url' => $sourceUrl];

        $media = Media::query()->updateOrCreate($identity, [
            'source_url' => $sourceUrl,
            'filename' => $this->nullableString($sourceImage['name'] ?? null),
            'alt_text' => $this->cleanText($sourceImage['alt'] ?? null),
            'metadata' => [
                'thumbnail_url' => $sourceImage['thumbnail'] ?? null,
            ],
        ]);

        if ($downloadMedia && $media->path === null) {
            $this->downloadMedia($media, $sleepMilliseconds);
        }

        return $media;
    }

    private function downloadMedia(Media $media, int $sleepMilliseconds): void
    {
        if ($media->source_url === null || ! str_starts_with($media->source_url, 'https://cofesti.ru/')) {
            throw new \UnexpectedValueException('Источник изображения не относится к разрешённому домену.');
        }

        $response = Http::connectTimeout(5)
            ->timeout(20)
            ->retry([250, 750])
            ->get($media->source_url)
            ->throw();
        $body = $response->body();

        if (strlen($body) > 15 * 1024 * 1024) {
            throw new \UnexpectedValueException('Изображение превышает лимит 15 МБ.');
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($body);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (! isset($extensions[$mimeType])) {
            throw new \UnexpectedValueException('Неподдерживаемый тип изображения.');
        }

        $dimensions = getimagesizefromstring($body);

        if ($dimensions === false) {
            throw new \UnexpectedValueException('Не удалось проверить изображение.');
        }

        $path = sprintf('catalog/%s.%s', $media->source_id ?? sha1($media->source_url), $extensions[$mimeType]);
        Storage::disk('public')->put($path, $body);

        $media->update([
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $mimeType,
            'byte_size' => strlen($body),
            'width' => $dimensions[0],
            'height' => $dimensions[1],
        ]);

        $this->pause($sleepMilliseconds);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function request(string $path, array $query): Response
    {
        return Http::acceptJson()
            ->connectTimeout(5)
            ->timeout(20)
            ->retry([250, 750], throw: false)
            ->get(rtrim((string) config('services.cofesti_import.base_url'), '/').$path, $query)
            ->throw();
    }

    /**
     * @param  list<mixed>  $sourceAttributes
     * @return list<array{name: string, taxonomy: string|null, terms: list<array{id: int|null, name: string, slug: string|null, default: bool}>}>
     */
    private function normalizeAttributes(array $sourceAttributes): array
    {
        $attributes = [];

        foreach ($sourceAttributes as $sourceAttribute) {
            if (! is_array($sourceAttribute) || ! isset($sourceAttribute['name'])) {
                continue;
            }

            $terms = [];
            foreach ($sourceAttribute['terms'] ?? [] as $sourceTerm) {
                if (! is_array($sourceTerm) || ! isset($sourceTerm['name'])) {
                    continue;
                }

                $terms[] = [
                    'id' => isset($sourceTerm['id']) ? (int) $sourceTerm['id'] : null,
                    'name' => (string) $sourceTerm['name'],
                    'slug' => $this->nullableString($sourceTerm['slug'] ?? null),
                    'default' => (bool) ($sourceTerm['default'] ?? false),
                ];
            }

            $attributes[] = [
                'name' => (string) $sourceAttribute['name'],
                'taxonomy' => $this->nullableString($sourceAttribute['taxonomy'] ?? null),
                'terms' => $terms,
            ];
        }

        return $attributes;
    }

    /**
     * @param  list<mixed>  $sourceAttributes
     * @return array<string, string|null>
     */
    private function normalizeVariantAttributes(array $sourceAttributes): array
    {
        $attributes = [];

        foreach ($sourceAttributes as $sourceAttribute) {
            if (! is_array($sourceAttribute) || ! isset($sourceAttribute['name'])) {
                continue;
            }

            $attributes[(string) $sourceAttribute['name']] = $this->nullableString($sourceAttribute['value'] ?? null);
        }

        return $attributes;
    }

    /**
     * @param  list<mixed>  $sourceAttributes
     */
    private function variantName(array $sourceAttributes): ?string
    {
        $attributes = $this->normalizeVariantAttributes($sourceAttributes);
        $values = array_filter($attributes, fn (?string $value): bool => $value !== null);

        return $values === [] ? null : implode(', ', $values);
    }

    /**
     * @param  array<string, mixed>  $sourceProduct
     */
    private function stockStatus(array $sourceProduct): StockStatus
    {
        return match ($sourceProduct['is_in_stock'] ?? null) {
            true => StockStatus::Available,
            false => StockStatus::OutOfStock,
            default => StockStatus::Unknown,
        };
    }

    private function cleanText(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $text = trim((string) preg_replace('/\\s+/u', ' ', strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));

        return $text === '' ? null : $text;
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function slugFromPermalink(string $permalink): string
    {
        $path = parse_url($permalink, PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', trim((string) $path, '/'))));
        $slug = rawurldecode((string) end($segments));

        if ($slug === '') {
            throw new \UnexpectedValueException('Не удалось извлечь slug из permalink товара.');
        }

        return $slug;
    }

    private function pause(int $sleepMilliseconds): void
    {
        if ($sleepMilliseconds > 0) {
            usleep($sleepMilliseconds * 1000);
        }
    }
}
