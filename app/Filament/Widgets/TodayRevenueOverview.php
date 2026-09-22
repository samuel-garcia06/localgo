<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayRevenueOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Resumen del día';

    protected ?string $description = null;

    protected function getListeners(): array
    {
        return [
            'echo-private:orders,order.updated' => '$refresh',
            'order-status-changed' => '$refresh',
        ];
    }

    protected function getColumns(): int|array|null
    {
        return [
            'sm' => 2,
            'md' => 4,
            'xl' => 7,
        ];
    }

    protected function getStats(): array
    {
        // Ventana operativa: últimas 24 horas (igual que las tablas)
        $cutoff = now()->subHours(24);

        // Ventana de hoy (desde medianoche) para métricas financieras
        $todayStart = today();

        // Statuses con valor económico
        $revenueStatuses = [
            OrderStatus::Confirmed->value,
            OrderStatus::Pending->value,
            OrderStatus::Accepted->value,
            OrderStatus::Delivered->value,
        ];

        // ── Métricas financieras (pedidos creados hoy) ──────────────────────
        $revenue = (float) Order::whereDate('created_at', $todayStart)
            ->whereIn('status', $revenueStatuses)
            ->sum('total');

        $revenueCount = Order::whereDate('created_at', $todayStart)
            ->whereIn('status', $revenueStatuses)
            ->count();

        $totalToday = Order::whereDate('created_at', $todayStart)->count();

        $avgTicket = $revenueCount > 0 ? round($revenue / $revenueCount, 2) : 0.0;

        $lastOrder = Order::whereDate('created_at', $todayStart)
            ->latest('created_at')
            ->value('created_at');

        $lastOrderDesc = $lastOrder
            ? 'Último: '.Carbon::parse($lastOrder)->format('H:i')
            : 'Sin pedidos hoy';

        // ── Contadores operativos (últimas 24h, misma fuente que las tablas) ─
        $pending = Order::whereIn('status', [OrderStatus::Confirmed->value, OrderStatus::Pending->value])
            ->where('created_at', '>=', $cutoff)
            ->count();

        $accepted = Order::where('status', OrderStatus::Accepted->value)
            ->where('created_at', '>=', $cutoff)
            ->count();

        // ── Acciones del día (por updated_at: cuenta el momento real de la acción) ─
        $rejectedToday = Order::where('status', OrderStatus::Rejected->value)
            ->whereDate('updated_at', $todayStart)
            ->count();

        $deliveredToday = Order::where('status', OrderStatus::Delivered->value)
            ->whereDate('updated_at', $todayStart)
            ->count();

        return [
            Stat::make('Facturado hoy', number_format($revenue, 2).' €')
                ->description('Importe acumulado del día')
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Ticket medio', number_format($avgTicket, 2).' €')
                ->description($revenueCount > 0 ? "Sobre {$revenueCount} pedidos" : 'Sin datos aún')
                ->icon('heroicon-o-calculator')
                ->color('primary'),

            Stat::make('Pedidos hoy', (string) $totalToday)
                ->description($lastOrderDesc)
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),

            Stat::make('Pendientes', (string) $pending)
                ->description('Esperando acción (24h)')
                ->icon('heroicon-o-clock')
                ->color($pending > 0 ? 'warning' : 'gray'),

            Stat::make('En cocina', (string) $accepted)
                ->description('Aceptados, por entregar (24h)')
                ->icon('heroicon-o-fire')
                ->color($accepted > 0 ? 'info' : 'gray'),

            Stat::make('Rechazados', (string) $rejectedToday)
                ->description('Rechazados hoy')
                ->icon('heroicon-o-x-circle')
                ->color($rejectedToday > 0 ? 'danger' : 'gray'),

            Stat::make('Entregados', (string) $deliveredToday)
                ->description('Entregados hoy')
                ->icon('heroicon-o-truck')
                ->color($deliveredToday > 0 ? 'success' : 'gray'),
        ];
    }
}
