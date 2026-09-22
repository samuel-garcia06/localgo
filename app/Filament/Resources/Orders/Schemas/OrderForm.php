<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_name')
                    ->label('Cliente')
                    ->disabled(),
                TextInput::make('customer_phone')
                    ->label('Teléfono')
                    ->disabled(),
                Select::make('delivery_type')
                    ->label('Tipo de entrega')
                    ->options(collect(DeliveryType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all())
                    ->disabled(),
                TextInput::make('customer_address')
                    ->label('Dirección')
                    ->disabled()
                    ->columnSpanFull(),
                TextInput::make('payment_method')
                    ->label('Método de pago')
                    ->disabled(),
                Select::make('payment_status')
                    ->label('Estado de pago')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all())
                    ->required(),
                Select::make('status')
                    ->label('Estado del pedido')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])->all())
                    ->required(),
                TextInput::make('total')
                    ->label('Total')
                    ->disabled(),
                Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
                Repeater::make('items')
                    ->relationship()
                    ->schema([
                        TextInput::make('product_name')->disabled(),
                        TextInput::make('drink_choice')->label('Bebida')->disabled(),
                        TextInput::make('sauce_choice')->label('Salsa')->disabled(),
                        TextInput::make('quantity')->disabled(),
                        TextInput::make('unit_price')->disabled(),
                        TextInput::make('subtotal')->disabled(),
                    ])
                    ->columns(6)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->columnSpanFull(),
            ]);
    }
}
