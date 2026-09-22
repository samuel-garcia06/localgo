<?php

namespace App\Filament\Widgets;

use App\Enums\RestaurantOperationalStatus;
use App\Models\RestaurantSetting;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class RestaurantStatusWidget extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.restaurant-status-widget';

    protected static bool $isLazy = false;

    public string $status = 'open';

    public function mount(): void
    {
        $this->status = RestaurantSetting::current()->operational_status->value;
    }

    public function setStatus(string $value): void
    {
        if (! RestaurantOperationalStatus::tryFrom($value)) {
            return;
        }

        RestaurantSetting::current()->update(['operational_status' => $value]);
        $this->status = $value;

        $status = RestaurantOperationalStatus::from($value);

        Notification::make()
            ->title("{$status->emoji()} {$status->label()}")
            ->body('Estado actualizado correctamente.')
            ->color($status->color())
            ->send();
    }

    public function currentStatus(): RestaurantOperationalStatus
    {
        return RestaurantOperationalStatus::from($this->status);
    }
}
