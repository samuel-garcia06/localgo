<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected array $extraBodyAttributes = [
        'class' => 'localgo-create-product-page',
    ];

    public function getTitle(): string|Htmlable
    {
        return 'Editar producto';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Los cambios estarán disponibles en la carta una vez guardes el producto.';
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
            DeleteAction::make()
                ->modalHeading('¿Eliminar este producto?')
                ->modalDescription('Esta acción no se puede deshacer. El producto desaparecerá de la carta de forma inmediata.')
                ->modalSubmitActionLabel('Sí, eliminar'),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Guardar cambios')
            ->extraAttributes([
                'class' => 'localgo-product-primary-action',
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
