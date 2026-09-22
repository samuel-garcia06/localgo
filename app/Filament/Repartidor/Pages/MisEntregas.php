<?php

namespace App\Filament\Repartidor\Pages;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MisEntregas extends Page
{
    protected static ?string $slug = '/';

    protected static ?string $title = 'Mis entregas';

    protected static ?string $navigationLabel = 'Mis entregas';

    protected string $view = 'filament.repartidor.pages.mis-entregas';

    public ?string $flashMessage = null;

    protected function getListeners(): array
    {
        return [
            'echo-private:orders,order.updated' => '$refresh',
        ];
    }

    public function getAvailableOrders()
    {
        return Order::query()
            ->where('delivery_type', DeliveryType::Delivery)
            ->where('status', OrderStatus::Ready)
            ->whereNull('repartidor_id')
            ->oldest('updated_at')
            ->get();
    }

    public function getMyOrders()
    {
        return Order::query()
            ->where('delivery_type', DeliveryType::Delivery)
            ->where('repartidor_id', Auth::id())
            ->whereIn('status', [OrderStatus::Ready, OrderStatus::Delivered])
            ->where('updated_at', '>=', now()->subHours(12))
            ->latest('updated_at')
            ->get();
    }

    public function tomarPedido(int $orderId): void
    {
        $order = Order::find($orderId);

        if (! $order) {
            return;
        }

        try {
            app(OrderService::class)->assignRepartidor($order, Auth::user());
            $this->flashMessage = "Pedido #{$order->id} asignado.";
        } catch (ValidationException $exception) {
            $this->flashMessage = collect($exception->errors())->flatten()->first()
                ?? 'No se pudo asignar el pedido.';
        }
    }

    public function marcarEntregado(int $orderId): void
    {
        $order = Order::find($orderId);

        if (! $order) {
            return;
        }

        if ($order->repartidor_id !== Auth::id()) {
            $this->flashMessage = 'Este pedido no está asignado a ti.';

            return;
        }

        try {
            app(OrderService::class)->updateOrderStatus($order, OrderStatus::Delivered);
            $this->flashMessage = "Pedido #{$order->id} marcado como entregado.";
        } catch (ValidationException $exception) {
            $this->flashMessage = collect($exception->errors())->flatten()->first()
                ?? 'No se pudo marcar el pedido como entregado.';
        }
    }
}
