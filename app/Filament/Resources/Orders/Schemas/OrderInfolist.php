<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\DeliveryType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    private const SHIPPING_LINE_NAMES = [
        'gastos de envio',
        'gastos de envío',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'xl' => 2,
            ])
            ->components([
                Section::make('Cliente')
                    ->description('Datos de contacto y entrega del pedido.')
                    ->extraAttributes([
                        'class' => 'localgo-order-section localgo-order-section-customer',
                    ])
                    ->schema([
                        TextEntry::make('customer_name')
                            ->label('Cliente')
                            ->weight('bold')
                            ->extraEntryWrapperAttributes([
                                'class' => 'localgo-order-highlight-entry',
                            ]),
                        TextEntry::make('customer_phone')->label('Teléfono'),
                        TextEntry::make('delivery_type')
                            ->label('Entrega')
                            ->formatStateUsing(fn (?DeliveryType $state): string => $state?->label() ?? 'No indicado')
                            ->extraEntryWrapperAttributes([
                                'class' => 'localgo-order-status-text',
                            ]),
                        TextEntry::make('customer_address')
                            ->label('Dirección')
                            ->placeholder('Recogida en local')
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label('Notas')
                            ->placeholder('Sin notas del cliente')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Pedido')
                    ->description('Resumen operativo y financiero del pedido.')
                    ->extraAttributes([
                        'class' => 'localgo-order-section localgo-order-section-summary',
                    ])
                    ->schema([
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->extraEntryWrapperAttributes([
                                'class' => 'localgo-order-status-text',
                            ]),
                        TextEntry::make('payment_status')
                            ->label('Estado de pago')
                            ->formatStateUsing(fn (?PaymentStatus $state): string => $state?->label() ?? 'No definido')
                            ->extraEntryWrapperAttributes([
                                'class' => 'localgo-order-status-text',
                            ]),
                        TextEntry::make('payment_method')
                            ->label('Pago')
                            ->badge()
                            ->formatStateUsing(fn (?PaymentMethod $state): string => $state?->label() ?? 'No definido')
                            ->color(fn (?PaymentMethod $state): string => match ($state) {
                                PaymentMethod::Cash => 'gray',
                                PaymentMethod::Stripe => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('total')
                            ->label('Total')
                            ->money('EUR')
                            ->extraEntryWrapperAttributes([
                                'class' => 'localgo-order-total',
                            ]),
                        TextEntry::make('created_at')->label('Creado')->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')->label('Último cambio')->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3),
                Section::make('Lineas del pedido')
                    ->description('Productos, cantidades y configuraciones elegidas por el cliente.')
                    ->extraAttributes([
                        'class' => 'localgo-order-section localgo-order-section-lines',
                    ])
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->state(fn ($record) => collect($record->items)
                                ->reject(fn ($item) => in_array(mb_strtolower(trim((string) $item->product_name)), self::SHIPPING_LINE_NAMES, true))
                                ->values()
                                ->all())
                            ->label('')
                            ->extraAttributes([
                                'class' => 'localgo-order-items',
                            ])
                            ->schema([
                                TextEntry::make('product_name')
                                    ->label('Producto')
                                    ->weight('bold')
                                    ->extraEntryWrapperAttributes([
                                        'class' => 'localgo-order-product-name',
                                    ]),
                                TextEntry::make('quantity')->label('Unidades'),
                                TextEntry::make('unit_price')->label('Precio unidad')->money('EUR'),
                                TextEntry::make('drink_choice')
                                    ->label('Bebida')
                                    ->hidden(fn ($state) => blank($state))
                                    ->columnSpan(1),
                                TextEntry::make('sauce_choice')
                                    ->label('Salsa')
                                    ->hidden(fn ($state) => blank($state))
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->contained(false),
                    ]),
            ]);
    }
}
