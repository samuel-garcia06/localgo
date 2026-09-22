<?php

namespace App\Models;

use App\Enums\RestaurantOperationalStatus;
use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    protected $fillable = [
        'default_preparation_time',
        'operational_status',
        'notification_sound',
        'allow_delivery',
        'allow_pickup',
        'minimum_order',
        'delivery_fee',
        'customer_notice',
    ];

    protected function casts(): array
    {
        return [
            'default_preparation_time' => 'integer',
            'operational_status' => RestaurantOperationalStatus::class,
            'allow_delivery' => 'boolean',
            'allow_pickup' => 'boolean',
            'minimum_order' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'default_preparation_time' => 20,
            'operational_status' => RestaurantOperationalStatus::Open,
            'notification_sound' => 'classic',
            'allow_delivery' => true,
            'allow_pickup' => true,
            'minimum_order' => 0,
            'delivery_fee' => 2.99,
            'customer_notice' => null,
        ]);
    }

    public function isOpen(): bool
    {
        return $this->operational_status === RestaurantOperationalStatus::Open;
    }

    public function isBusy(): bool
    {
        return $this->operational_status === RestaurantOperationalStatus::Busy;
    }

    public function isClosed(): bool
    {
        return $this->operational_status === RestaurantOperationalStatus::Closed;
    }

    public function acceptingOrders(): bool
    {
        return $this->operational_status->acceptingOrders();
    }
}
