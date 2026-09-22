<?php

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\RestaurantSetting;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class KitchenDisplay extends Component
{
    public array $acceptTimes = [];

    public ?int $selectedOrderId = null;

    public ?string $flashMessage = null;

    public int $defaultPreparationTime = 20;

    public int $newOrderCount = 0;

    public string $notificationSound = 'classic';

    public function mount(): void
    {
        $settings = RestaurantSetting::current();
        $this->defaultPreparationTime = $settings->default_preparation_time;
        $this->notificationSound = $settings->notification_sound ?? 'classic';
        $this->syncNewOrderCount();
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:orders,order.updated' => 'handleOrderUpdate',
        ];
    }

    public function handleOrderUpdate(): void
    {
        $this->syncNewOrderCount();
    }

    private function syncNewOrderCount(): void
    {
        $this->newOrderCount = Order::whereIn('status', [
            OrderStatus::Confirmed->value,
            OrderStatus::Pending->value,
        ])
            ->where('created_at', '>=', now()->subHours(12))
            ->count();
    }

    public function getOrders()
    {
        return Order::query()
            ->with('items')
            ->whereIn('status', [
                OrderStatus::Confirmed,
                OrderStatus::Pending,
                OrderStatus::Accepted,
                OrderStatus::Ready,
            ])
            ->where('created_at', '>=', now()->subHours(12))
            ->oldest('created_at')
            ->get();
    }

    public function selectedOrder(): ?Order
    {
        if ($this->selectedOrderId === null) {
            return null;
        }

        return Order::query()
            ->with('items')
            ->find($this->selectedOrderId);
    }

    public function selectOrder(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
    }

    public function closeDetails(): void
    {
        $this->selectedOrderId = null;
    }

    public function setAcceptTime(int $orderId, int $minutes): void
    {
        $this->acceptTimes[$orderId] = max(5, min(120, $minutes));
    }

    public function adjustAcceptTime(int $orderId, int $delta): void
    {
        $current = (int) ($this->acceptTimes[$orderId] ?? $this->defaultPreparationTime);

        $this->setAcceptTime($orderId, $current + $delta);
    }

    public function accept(int $orderId): void
    {
        $order = Order::find($orderId);

        if (! $order) {
            return;
        }

        if (! in_array($order->status, [OrderStatus::Confirmed, OrderStatus::Pending], true)) {
            return;
        }

        $prepTime = (int) ($this->acceptTimes[$orderId] ?? $this->defaultPreparationTime);

        try {
            app(OrderService::class)->updateOrderStatus($order, OrderStatus::Accepted, $prepTime);
            unset($this->acceptTimes[$orderId]);
            $this->flashMessage = "Pedido #{$order->id} aceptado con {$prepTime} min.";
            $this->syncNewOrderCount();
        } catch (ValidationException) {
            $this->flashMessage = 'No se pudo aceptar el pedido. Puede que ya haya cambiado de estado.';
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
            $this->flashMessage = "Pedido #{$order->id} rechazado.";
            $this->syncNewOrderCount();
        } catch (ValidationException) {
            $this->flashMessage = 'No se pudo rechazar el pedido. Puede que ya haya cambiado de estado.';
        }
    }

    public function advance(int $orderId): void
    {
        $order = Order::find($orderId);

        if (! $order) {
            return;
        }

        $next = match ($order->status) {
            OrderStatus::Accepted => OrderStatus::Ready,
            OrderStatus::Ready => OrderStatus::Delivered,
            default => null,
        };

        if ($next === null) {
            return;
        }

        try {
            app(OrderService::class)->updateOrderStatus($order, $next);
        } catch (ValidationException) {
            $this->flashMessage = 'No se pudo cambiar el estado. Puede que el pedido ya haya cambiado.';
        }
    }

    public function addTime(int $orderId, int $minutes): void
    {
        $order = Order::find($orderId);

        if (! $order) {
            return;
        }

        try {
            $updated = app(OrderService::class)->extendPreparationTime($order, $minutes);
            $this->flashMessage = "Pedido #{$updated->id}: tiempo actualizado a {$updated->preparation_time} min.";
        } catch (ValidationException) {
            $this->flashMessage = 'No se pudo añadir tiempo a este pedido.';
        }
    }

    public static function elapsedMinutes(Carbon $dt): int
    {
        return (int) floor($dt->diffInSeconds() / 60);
    }

    public static function elapsedLabel(Carbon $dt): string
    {
        $seconds = (int) $dt->diffInSeconds();
        $minutes = (int) floor($seconds / 60);
        $hours = (int) floor($minutes / 60);
        $days = (int) floor($hours / 24);

        if ($seconds < 60) {
            return 'Hace unos segundos';
        }

        if ($minutes < 60) {
            return "Hace {$minutes} min";
        }

        if ($hours < 24) {
            return $hours === 1 ? 'Hace 1 h' : "Hace {$hours} h";
        }

        return $days === 1 ? 'Hace 1 día' : "Hace {$days} días";
    }

    public function render()
    {
        return view('livewire.kitchen-display', [
            'orders' => $this->getOrders(),
            'selectedOrder' => $this->selectedOrder(),
        ])->layout('layouts.kitchen');
    }
}
