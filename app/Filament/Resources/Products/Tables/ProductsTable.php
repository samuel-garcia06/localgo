<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Foto')
                    ->disk('public')
                    ->visibility('public')
                    ->square()
                    ->height(64)
                    ->width(64)
                    ->extraImgAttributes([
                        'class' => 'rounded-xl object-cover',
                        'loading' => 'lazy',
                    ]),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),
                IconColumn::make('is_available')
                    ->label('Disponible')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Categoría')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_available')
                    ->label('Disponibilidad')
                    ->placeholder('Todos')
                    ->trueLabel('Solo disponibles')
                    ->falseLabel('No disponibles'),
            ])
            ->recordUrl(fn ($record) => ProductResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                EditAction::make()->label('Editar'),
            ])
            ->emptyStateHeading('Sin productos')
            ->emptyStateDescription('Añade el primer producto al catálogo.');
    }
}
