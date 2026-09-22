<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Forms\Components\IconPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'xl' => 2,
            ])
            ->components([
                Section::make('Datos de la categoría')
                    ->description('Define cómo aparecerá esta categoría en la carta del restaurante.')
                    ->columnSpan([
                        'xl' => 1,
                    ])
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre de la categoría')
                            ->placeholder('Ej. Hamburguesas, Bebidas, Postres...')
                            ->prefixIcon(Heroicon::OutlinedTag)
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', str($state)->slug()))
                            ->helperText('El nombre que verán los clientes al navegar por la carta.'),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->placeholder('Describe brevemente qué productos agrupa esta categoría.')
                            ->rows(4)
                            ->helperText('Opcional. Ayuda al cliente a entender qué encontrará en esta sección.'),
                    ])
                    ->columns(1),

                Section::make('Organización')
                    ->description('Controla el orden y la visibilidad de esta categoría en la carta.')
                    ->columnSpan([
                        'xl' => 1,
                    ])
                    ->schema([
                        TextInput::make('slug')
                            ->label('Slug')
                            ->placeholder('hamburguesas')
                            ->prefixIcon(Heroicon::OutlinedLink)
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Identificador interno para URLs. Se genera automáticamente desde el nombre. Solo modifícalo si es estrictamente necesario.'),
                        TextInput::make('sort_order')
                            ->label('Posición en la carta')
                            ->placeholder('0')
                            ->prefixIcon(Heroicon::OutlinedHashtag)
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->helperText('Número de orden de aparición. Menor número significa que aparece antes en la carta.'),
                        Toggle::make('is_active')
                            ->label('Categoría activa')
                            ->helperText('Desactívala para ocultarla temporalmente de la carta sin necesidad de eliminarla.')
                            ->default(true),
                    ])
                    ->columns(1),

                Section::make('Icono')
                    ->description('Elige el icono que aparecerá junto al nombre de la categoría en la carta pública. Si no seleccionas ninguno se usará un icono genérico.')
                    ->columnSpan([
                        'xl' => 2,
                    ])
                    ->schema([
                        IconPicker::make('icon')
                            ->label('Icono de la categoría')
                            ->helperText('Haz clic en el icono que mejor represente esta categoría.'),
                    ])
                    ->columns(1),
            ]);
    }
}
