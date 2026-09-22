<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'xl' => 3,
            ])
            ->components([

                // ── Row 1: Información básica (2/3) + Precio (1/3) ──────────
                Section::make('Información básica')
                    ->description('Define el producto tal y como aparecerá en la carta de Urban Bites.')
                    ->extraAttributes([
                        'class' => 'localgo-product-form-section localgo-product-form-basic',
                    ])
                    ->columnSpan([
                        'xl' => 2,
                    ])
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del producto')
                            ->placeholder('Ej. Menú Burger Clásica')
                            ->prefixIcon(Heroicon::OutlinedTag)
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', str($state)->slug()))
                            ->helperText('Usa un nombre claro y comercial para que el cliente lo identifique rápido.')
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->label('Categoría')
                            ->placeholder('Selecciona una categoría')
                            ->prefixIcon(Heroicon::OutlinedSquares2x2)
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->placeholder('menu-burger-clasica')
                            ->prefixIcon(Heroicon::OutlinedLink)
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Se genera automáticamente desde el nombre. Solo modifícalo si es necesario.'),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->placeholder('Describe ingredientes, estilo, extras incluidos o detalles que ayuden a vender mejor el producto.')
                            ->rows(5)
                            ->helperText('Una descripción breve y concreta mejora la conversión en la carta.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Precio')
                    ->description('Ajusta el importe final que verá el cliente.')
                    ->extraAttributes([
                        'class' => 'localgo-product-form-section localgo-product-form-pricing',
                    ])
                    ->columnSpan([
                        'xl' => 1,
                    ])
                    ->schema([
                        TextInput::make('price')
                            ->label('Precio de venta')
                            ->numeric()
                            ->prefix('€')
                            ->placeholder('12.90')
                            ->required()
                            ->helperText('Introduce el precio final con impuestos incluidos.'),
                    ]),

                // ── Row 2: Imagen (2/3) + Disponibilidad (1/3) ──────────────
                Section::make('Imagen del producto')
                    ->description('Sube una imagen atractiva para mejorar la presentación en la carta.')
                    ->extraAttributes([
                        'class' => 'localgo-product-form-section localgo-product-form-image',
                    ])
                    ->columnSpan([
                        'xl' => 2,
                    ])
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Imagen principal')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('products')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth(1600)
                            ->imageResizeTargetHeight(1600)
                            ->getUploadedFileNameForStorageUsing(
                                fn ($file): string => Str::uuid().'.'.($file->guessExtension() ?: 'jpg')
                            )
                            ->helperText('Arrastra una imagen o haz clic para subirla. Se mostrará en la carta del restaurante.'),
                    ]),

                Section::make('Disponibilidad')
                    ->description('Controla si este producto puede ser pedido desde la carta.')
                    ->extraAttributes([
                        'class' => 'localgo-product-form-section localgo-product-form-availability',
                    ])
                    ->columnSpan([
                        'xl' => 1,
                    ])
                    ->schema([
                        Toggle::make('is_available')
                            ->label('Producto disponible')
                            ->helperText('Actívalo para que el producto pueda mostrarse y pedirse desde la carta.')
                            ->default(true),
                    ]),

                // ── Row 3: Opciones de menú (3/3 full width) ─────────────────
                Section::make('Opciones de menú')
                    ->description('Configura las opciones adicionales si este producto es un menú con bebidas o salsas.')
                    ->extraAttributes([
                        'class' => 'localgo-product-form-section localgo-product-form-options',
                    ])
                    ->columnSpan([
                        'xl' => 3,
                    ])
                    ->schema([
                        TextInput::make('drink_option_count')
                            ->label('Número de bebidas')
                            ->numeric()
                            ->placeholder('0')
                            ->default(0)
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->helperText('Indica cuántas bebidas puede elegir el cliente si este producto es un menú.')
                            ->afterStateUpdated(fn ($state, $set) => $set('has_drink_option', (int) $state > 0)),
                        TextInput::make('sauce_option_count')
                            ->label('Número de salsas')
                            ->numeric()
                            ->placeholder('0')
                            ->default(0)
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->helperText('Úsalo cuando el producto permita seleccionar salsas adicionales.')
                            ->afterStateUpdated(fn ($state, $set) => $set('has_sauce_option', (int) $state > 0)),
                        Hidden::make('has_drink_option')
                            ->dehydrated(true)
                            ->default(false),
                        Hidden::make('has_sauce_option')
                            ->dehydrated(true)
                            ->default(false),
                    ])
                    ->columns(2),

            ]);
    }
}
