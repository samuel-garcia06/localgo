<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Validation\ValidationException;

class InProgressOrdersWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getListeners(): array
    {
        return [
            'echo-private:orders,order.updated' => '$refresh',
            'order-status-changed' => '$refresh',
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->whereIn('status', [OrderStatus::Accepted, OrderStatus::Ready])
                    ->where('created_at', '>=', now()->subHours(24))
                    ->latest('created_at')
            )
            ->heading('Pedidos en preparación')
            ->description('Pedidos aceptados de las últimas 24 horas pendientes de ser entregados.')
            ->poll('10s')
            ->paginated(false)
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->wrap(),
                TextColumn::make('customer_phone')
                    ->label('Teléfono')
                    ->copyable(),
                TextColumn::make('delivery_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '—'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('preparation_time')
                    ->label('Tiempo est.')
                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state} min" : '—')
                    ->alignCenter(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR')
                    ->alignEnd(),
                TextColumn::make('created_at')
                    ->label('Hora')
                    ->dateTime('H:i')
                    ->sortable(),
                TextColumn::make('waiting_time')
                    ->label('Esperando')
                    ->getStateUsing(fn (Order $record): string => self::elapsedTime($record->created_at))
                    ->badge()
                    ->color(fn (Order $record): string => (int) floor($record->created_at->diffInSeconds() / 60) > 30 ? 'danger' : 'success'),
            ])
            ->recordActions([
                Action::make('markReady')
                    ->label('Listo')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->button()
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::Accepted)
                    ->action(fn (Order $record) => $this->changeStatus($record, OrderStatus::Ready)),
                Action::make('deliver')
                    ->label('Entregar')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->button()
                    ->visible(fn (Order $record): bool => in_array($record->status, [OrderStatus::Accepted, OrderStatus::Ready], true))
                    ->action(fn (Order $record) => $this->changeStatus($record, OrderStatus::Delivered)),
                ViewAction::make()
                    ->label('Ver')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateIcon('heroicon-o-truck')
            ->emptyStateHeading('Sin pedidos en preparación')
            ->emptyStateDescription('Los pedidos aceptados aparecerán aquí hasta ser entregados.');
    }

    private static function elapsedTime(Carbon $dt): string
    {
        // diffInMinutes() devuelve float en Carbon 3; usamos diffInSeconds() y floor() para int limpio
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

    private function changeStatus(Order $order, OrderStatus $status): void
    {
        try {
            app(OrderService::class)->updateOrderStatus($order, $status);
            $this->dispatch('order-status-changed');
        } catch (ValidationException $e) {
            Notification::make()
                ->title('No se pudo cambiar el estado')
                ->body(collect($e->errors())->flatten()->first() ?? 'Cambio de estado no válido.')
                ->warning()
                ->send();
        }
    }
}
