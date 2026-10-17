<?php

namespace App\Console\Commands;

use App\Actions\SyncPublicCatalog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('cofesti:import-public
    {--download-media : Скачать изображения в собственное хранилище}
    {--limit= : Ограничить количество импортируемых товаров для проверки}
    {--page-size=50 : Размер страницы публичного API от 1 до 100}
    {--sleep=250 : Пауза между запросами в миллисекундах}')]
#[Description('Импортирует публичные категории и товары COFESTI без создания дублей.')]
class ImportPublicCatalog extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SyncPublicCatalog $syncPublicCatalog): int
    {
        $pageSize = min(100, max(1, (int) $this->option('page-size')));
        $sleepMilliseconds = max(0, (int) $this->option('sleep'));
        $limit = $this->option('limit') === null ? null : max(1, (int) $this->option('limit'));

        $report = $syncPublicCatalog->handle(
            downloadMedia: (bool) $this->option('download-media'),
            pageSize: $pageSize,
            sleepMilliseconds: $sleepMilliseconds,
            limit: $limit,
        );
        $report['imported_at'] = now()->toIso8601String();
        $report['downloaded_media'] = (bool) $this->option('download-media');
        $reportPath = 'imports/cofesti-public-'.now()->format('Ymd-His').'.json';

        Storage::disk('local')->put(
            $reportPath,
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        );

        $this->table(
            ['Категории', 'Товары', 'Варианты', 'Изображения', 'Ошибки'],
            [[
                $report['categories'],
                $report['products'],
                $report['variants'],
                $report['media'],
                count($report['errors']),
            ]],
        );
        $this->line('Отчёт: storage/app/private/'.$reportPath);

        foreach ($report['errors'] as $error) {
            $this->warn($error);
        }

        return self::SUCCESS;
    }
}
