<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a pedidos')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->url(OrderResource::getUrl('index'))
                ->color('gray')
                ->extraAttributes([
                    'class' => 'localgo-back-orders',
                ]),
        ];
    }

    protected function resolveRecord(int|string $key): Order
    {
        return Order::query()
            ->whereIn('status', OrderStatus::visibleToRestaurantValues())
            ->findOrFail($key);
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:orders,order.updated' => '$refresh',
        ];
    }
}
