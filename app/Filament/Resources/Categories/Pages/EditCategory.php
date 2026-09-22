<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getTitle(): string|Htmlable
    {
        return 'Editar categoría';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Los cambios se aplicarán en la carta en cuanto guardes.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->url(CategoryResource::getUrl('index'))
                ->color('gray'),
            DeleteAction::make()
                ->modalHeading('¿Eliminar esta categoría?')
                ->modalDescription('Solo se puede eliminar si no tiene productos asociados.')
                ->modalSubmitActionLabel('Sí, eliminar')
                ->before(function (DeleteAction $action): void {
                    if (! $this->record->products()->exists()) {
                        return;
                    }

                    Notification::make()
                        ->title('No se puede eliminar la categoría')
                        ->body('Tiene productos asociados. Mueve o elimina esos productos antes de borrar la categoría.')
                        ->warning()
                        ->send();

                    $action->halt();
                }),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Guardar cambios');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar');
    }
}
