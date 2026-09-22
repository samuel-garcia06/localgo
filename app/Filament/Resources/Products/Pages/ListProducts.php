<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        return 'Gestiona todos los productos disponibles en la carta del restaurante.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo producto'),
        ];
    }
}
