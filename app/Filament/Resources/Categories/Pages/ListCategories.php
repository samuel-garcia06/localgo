<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        return 'Organiza la carta agrupando los productos en secciones.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nueva categoría'),
        ];
    }
}
