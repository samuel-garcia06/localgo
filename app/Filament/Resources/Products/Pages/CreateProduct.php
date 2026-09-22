<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected array $extraBodyAttributes = [
        'class' => 'localgo-create-product-page',
    ];

    public function getTitle(): string|Htmlable
    {
        return 'Crear producto';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Los cambios estarán disponibles en la carta una vez publiques este producto.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->url(ProductResource::getUrl('index'))
                ->color('gray')
                ->extraAttributes([
                    'class' => 'localgo-product-back-action',
                ]),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Guardar producto')
            ->extraAttributes([
                'class' => 'localgo-product-primary-action',
            ]);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Guardar y crear otro')
            ->extraAttributes([
                'class' => 'localgo-product-secondary-action',
            ]);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar')
            ->extraAttributes([
                'class' => 'localgo-product-tertiary-action',
            ]);
    }
}
