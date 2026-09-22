<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Resources\Categories\CategoryResource;
use App\Support\LocalgoStore;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('icon')
                    ->label('')
                    ->formatStateUsing(fn (?string $state) => LocalgoStore::categoryIconForKey($state))
                    ->alignCenter()
                    ->width('3rem'),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Productos')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
            ])
            ->recordUrl(fn ($record) => CategoryResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                EditAction::make()->label('Editar'),
            ])
            ->emptyStateHeading('Sin categorías')
            ->emptyStateDescription('Crea la primera categoría para organizar la carta.');
    }
}
