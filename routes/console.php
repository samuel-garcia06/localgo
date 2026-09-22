<?php

use App\Services\OrderEmailConfirmationService;
use App\Services\ProductImageAssignmentService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:cleanup-email-confirmations', function (OrderEmailConfirmationService $service) {
    $deleted = $service->cleanupExpiredPendingOrders();

    $this->info("Pedidos pendientes de email expirados eliminados: {$deleted}");
})->purpose('Delete expired pending email-confirmation orders');

Artisan::command('products:assign-images {--force : Reasignar imagen incluso si el producto ya tiene una}', function (ProductImageAssignmentService $service) {
    $result = $service->assign((bool) $this->option('force'), $this);
    $summary = $service->sourceSummary();

    $this->info("Imagenes descargadas/disponibles: {$result['downloaded']}");
    $this->info("Productos actualizados: {$result['updated']}");
    $this->info("Productos omitidos: {$result['skipped']}");
    $this->info("Productos sin imagen valida: {$result['missing']}");
    $this->line("Origen principal: {$summary['project_json']}");
    $this->line("Almacenadas en: {$summary['downloaded_to']}");
    $this->line("URL publica: {$summary['public_url_prefix']}");

    return $result['missing'] === 0 ? self::SUCCESS : self::FAILURE;
})->purpose('Assign local food photos to every product');

Schedule::command('orders:cleanup-email-confirmations')->hourly();
