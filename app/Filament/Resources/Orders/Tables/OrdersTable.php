<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('customer_phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('delivery_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof DeliveryType ? $state->label() : (string) $state),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('created_at')
                    ->label('Recibido')
                    ->dateTime('d/m H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(
                        collect(OrderStatus::visibleToRestaurantValues())
                            ->mapWithKeys(fn ($value) => [$value => OrderStatus::from($value)->getLabel()])
                            ->all()
                    ),
            ])
            ->recordActions([
                Action::make('accept')
                    ->label('Aceptar')
                    ->color('success')
                    ->button()
                    ->visible(fn ($record) => in_array($record->status, [OrderStatus::Confirmed, OrderStatus::Pending], true))
                    ->action(fn ($record) => self::updateStatus($record, OrderStatus::Accepted)),
                Action::make('reject')
                    ->label('Rechazar')
                    ->color('danger')
                    ->button()
                    ->visible(fn ($record) => in_array($record->status, [OrderStatus::Confirmed, OrderStatus::Pending], true))
                    ->action(fn ($record) => self::updateStatus($record, OrderStatus::Rejected)),
                Action::make('delivered')
                    ->label('Entregado')
                    ->color('gray')
                    ->button()
                    ->visible(fn ($record) => $record->status === OrderStatus::Accepted)
                    ->action(fn ($record) => self::updateStatus($record, OrderStatus::Delivered)),
                ViewAction::make()->label('Ver'),
            ])
            ->emptyStateHeading('Sin pedidos')
            ->emptyStateDescription('Los nuevos pedidos aparecerán aquí cuando lleguen.');
    }

    private static function updateStatus($record, OrderStatus $status): void
    {
        try {
            app(OrderService::class)->updateOrderStatus($record, $status);
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('No se pudo cambiar el estado')
                ->body(collect($exception->errors())->flatten()->first() ?? 'El cambio de estado no es válido.')
                ->warning()
                ->send();
        }
    }
}
