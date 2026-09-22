<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getTitle(): string|Htmlable
    {
        return 'Nueva categoría';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'La categoría aparecerá en la carta en cuanto la guardes como activa.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->url(CategoryResource::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Guardar categoría');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Guardar y crear otra');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar');
    }
}
