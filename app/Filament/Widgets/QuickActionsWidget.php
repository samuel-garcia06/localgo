<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.quick-actions';

    public function getViewData(): array
    {
        return [
            'actions' => [
                [
                    'label' => 'Ver pedidos',
                    'icon' => 'heroicon-o-shopping-bag',
                    'url' => OrderResource::getUrl('index'),
                    'color' => 'primary',
                    'target' => '_self',
                ],
                [
                    'label' => 'Vista cocina',
                    'icon' => 'heroicon-o-computer-desktop',
                    'url' => route('kitchen'),
                    'color' => 'warning',
                    'target' => '_blank',
                ],
                [
                    'label' => 'Crear producto',
                    'icon' => 'heroicon-o-plus-circle',
                    'url' => ProductResource::getUrl('create'),
                    'color' => 'success',
                    'target' => '_self',
                ],
                [
                    'label' => 'Crear categoría',
                    'icon' => 'heroicon-o-folder-plus',
                    'url' => CategoryResource::getUrl('create'),
                    'color' => 'info',
                    'target' => '_self',
                ],
            ],
        ];
    }
}
