<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class TotalOrders extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Histórico de pedidos';

    protected static ?string $title = 'Histórico de pedidos';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.total-orders';

    public function getViewData(): array
    {
        return [
            'stats' => [
                [
                    'label' => 'Pedidos históricos',
                    'value' => (string) Order::query()->whereIn('status', OrderStatus::visibleToRestaurantValues())->count(),
                    'description' => 'Todos los pedidos registrados',
                ],
                [
                    'label' => 'Pendientes históricos',
                    'value' => (string) Order::query()->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Pending])->count(),
                    'description' => 'Pedidos aún no resueltos',
                ],
                [
                    'label' => 'Aceptados históricos',
                    'value' => (string) Order::query()->where('status', OrderStatus::Accepted)->count(),
                    'description' => 'Pedidos aceptados en el histórico',
                ],
                [
                    'label' => 'Facturado histórico',
                    'value' => number_format((float) Order::query()->whereIn('status', OrderStatus::visibleToRestaurantValues())->sum('total'), 2).' EUR',
                    'description' => 'Total facturado acumulado',
                ],
            ],
        ];
    }

    public function table(Table $table): Table
    {
        return OrdersTable::configure($table)
            ->query(
                Order::query()
                    ->whereIn('status', OrderStatus::visibleToRestaurantValues())
                    ->latest('created_at')
            )
            ->poll(null)
            ->recordUrl(fn ($record) => OrderResource::getUrl('view', ['record' => $record]))
            ->recordActions([]);
    }
}
