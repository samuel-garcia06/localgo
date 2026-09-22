<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationalStatusWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Estado operativo';

    protected ?string $description = null;

    protected function getColumns(): int|array|null
    {
        return [
            'sm' => 3,
            'xl' => 3,
        ];
    }

    protected function getStats(): array
    {
        $availableProducts = Product::where('is_available', true)->count();
        $activeCategories = Category::where('is_active', true)->count();
        $pendingAll = Order::whereIn('status', [OrderStatus::Confirmed, OrderStatus::Pending])
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        return [
            Stat::make('Productos disponibles', (string) $availableProducts)
                ->description($availableProducts > 0 ? 'En carta ahora mismo' : 'Sin productos activos')
                ->icon('heroicon-o-tag')
                ->color($availableProducts > 0 ? 'success' : 'danger'),

            Stat::make('Categorías activas', (string) $activeCategories)
                ->description($activeCategories > 0 ? 'Visibles en la carta' : 'Sin categorías activas')
                ->icon('heroicon-o-squares-2x2')
                ->color($activeCategories > 0 ? 'success' : 'danger'),

            Stat::make('Pendientes activos', (string) $pendingAll)
                ->description($pendingAll > 0 ? 'Sin resolver (24h)' : 'Ninguno pendiente')
                ->icon('heroicon-o-clock')
                ->color($pendingAll > 0 ? 'warning' : 'success'),
        ];
    }
}
