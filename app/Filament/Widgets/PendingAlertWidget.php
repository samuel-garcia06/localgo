<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Widgets\Widget;

class PendingAlertWidget extends Widget
{
    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.pending-alert';

    protected static bool $isLazy = false;

    public int $pendingCount = 0;

    public string $avgWait = '';

    public string $ordersUrl = '';

    public function mount(): void
    {
        $this->ordersUrl = OrderResource::getUrl('index');
        $this->refreshData();
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:orders,order.updated' => 'refreshData',
            'order-status-changed' => 'refreshData',
        ];
    }

    public function refreshData(): void
    {
        $orders = Order::whereIn('status', [OrderStatus::Confirmed->value, OrderStatus::Pending->value])
            ->where('created_at', '>=', now()->subHours(24))
            ->get(['id', 'created_at']);

        $this->pendingCount = $orders->count();
        $this->avgWait = '';

        if ($this->pendingCount > 0) {
            $avgMinutes = (int) round($orders->avg(fn ($o) => (int) floor($o->created_at->diffInSeconds() / 60)));

            if ($avgMinutes < 1) {
                $this->avgWait = 'menos de 1 min';
            } elseif ($avgMinutes < 60) {
                $this->avgWait = "{$avgMinutes} min";
            } else {
                $h = (int) floor($avgMinutes / 60);
                $m = $avgMinutes % 60;
                $this->avgWait = $m > 0 ? "{$h}h {$m}min" : "{$h}h";
            }
        }
    }
}
