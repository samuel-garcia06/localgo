<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\RestaurantSetting;
use Filament\Widgets\Widget;

class SoundAlertWidget extends Widget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.sound-alert';

    protected static bool $isLazy = false;

    public int $pendingCount = 0;

    public array $latestOrder = [];

    public string $notificationSound = 'classic';

    public function mount(): void
    {
        // datos iniciales cargados en getViewData()
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:orders,order.updated' => 'refreshData',
            'order-status-changed' => 'refreshData',
        ];
    }

    /**
     * Se ejecuta en cada render (poll, eventos, etc.) para mantener las props frescas.
     * Permite usar wire:poll sin especificar método, alineado con el patrón Filament v5.
     */
    protected function getViewData(): array
    {
        $this->refreshData();

        return [];
    }

    public function refreshData(): void
    {
        $settings = RestaurantSetting::current();
        $this->notificationSound = $settings->notification_sound ?? 'classic';

        $orders = Order::whereIn('status', [OrderStatus::Confirmed->value, OrderStatus::Pending->value])
            ->where('created_at', '>=', now()->subHours(24))
            ->latest('created_at')
            ->get(['customer_name', 'total', 'created_at']);

        $this->pendingCount = $orders->count();

        if ($orders->isNotEmpty()) {
            $latest = $orders->first();
            $this->latestOrder = [
                'name' => $latest->customer_name,
                'total' => number_format((float) $latest->total, 2, ',', '.').' €',
                'time' => $latest->created_at->format('H:i'),
            ];
        } else {
            $this->latestOrder = [];
        }
    }
}
