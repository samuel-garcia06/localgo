<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\RestaurantSetting;
use App\Services\OrderService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Validation\ValidationException;

class PendingOrdersWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.pending-orders-widget';

    public int $defaultPreparationTime = 20;

    public array $preparationTimes = [];

    protected function getListeners(): array
    {
        return [
            'echo-private:orders,order.updated' => 'refreshOrders',
            'order-status-changed' => 'refreshOrders',
        ];
    }

    public function mount(): void
    {
        $this->defaultPreparationTime = RestaurantSetting::current()->default_preparation_time;
    }

    public function refreshOrders(): void
    {
        // Livewire re-renders the component; component state ($preparationTimes) is preserved.
    }

    public function getPendingOrders()
    {
        return Order::query()
            ->with('items')
            ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Pending])
            ->where('created_at', '>=', now()->subHours(24))
            ->latest('created_at')
            ->get();
    }

    public function getTimeFor(int $orderId): int
    {
        return $this->preparationTimes[$orderId] ?? $this->defaultPreparationTime;
    }

    public function increment(int $orderId): void
    {
        $current = $this->getTimeFor($orderId);
        $this->preparationTimes[$orderId] = min(120, $current + 5);
    }

    public function decrement(int $orderId): void
    {
        $current = $this->getTimeFor($orderId);
        $this->preparationTimes[$orderId] = max(5, $current - 5);
    }

    public function setTime(int $orderId, int $time): void
    {
        $this->preparationTimes[$orderId] = $time;
    }

    public function accept(int $orderId): void
    {
        $order = Order::find($orderId);

        if (! $order) {
            return;
        }

        $prepTime = $this->getTimeFor($orderId);

        try {
            app(OrderService::class)->updateOrderStatus($order, OrderStatus::Accepted, $prepTime);
            unset($this->preparationTimes[$orderId]);
            $this->dispatch('order-status-changed');
            Notification::make()
                ->title("Pedido #{$order->id} aceptado")
                ->body("Tiempo estimado: {$prepTime} minutos.")
                ->success()
                ->send();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('No se pudo aceptar el pedido')
                ->body(collect($e->errors())->flatten()->first() ?? 'Cambio de estado no válido.')
                ->warning()
                ->send();
        }
    }

    public function reject(int $orderId): void
    {
        $order = Order::find($orderId);

        if (! $order) {
            return;
        }

        try {
            app(OrderService::class)->updateOrderStatus($order, OrderStatus::Rejected);
            unset($this->preparationTimes[$orderId]);
            $this->dispatch('order-status-changed');
            Notification::make()
                ->title("Pedido #{$order->id} rechazado")
                ->danger()
                ->send();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('No se pudo rechazar el pedido')
                ->body(collect($e->errors())->flatten()->first() ?? 'Cambio de estado no válido.')
                ->warning()
                ->send();
        }
    }

    private static function elapsedTime(Carbon $dt): string
    {
        $seconds = (int) $dt->diffInSeconds();
        $m = (int) floor($seconds / 60);

        if ($m < 1) {
            return 'Ahora mismo';
        }
        if ($m < 60) {
            return "Hace {$m} min";
        }
        $h = (int) floor($m / 60);
        $r = $m % 60;

        return $r > 0 ? "Hace {$h}h {$r}min" : "Hace {$h}h";
    }

    protected function getViewData(): array
    {
        $orders = $this->getPendingOrders();

        return [
            'orders' => $orders,
            'defaultPreparationTime' => $this->defaultPreparationTime,
            'preparationTimes' => $this->preparationTimes,
            'elapsedFn' => fn (Carbon $dt) => self::elapsedTime($dt),
        ];
    }
}
