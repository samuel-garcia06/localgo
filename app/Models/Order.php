<?php

namespace App\Models;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'public_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'delivery_type',
        'repartidor_id',
        'delivered_at',
        'payment_method',
        'payment_status',
        'status',
        'notes',
        'preparation_time',
        'total',
        'email_confirmation_token_hash',
        'email_confirmation_expires_at',
        'email_confirmed_at',
        'verified_at',
        'confirmation_sent_at',
        'confirmation_resend_count',
    ];

    protected $hidden = [
        'checkout_token',
        'email_confirmation_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'delivery_type' => DeliveryType::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'status' => OrderStatus::class,
            'preparation_time' => 'integer',
            'total' => 'decimal:2',
            'delivered_at' => 'datetime',
            'email_confirmation_expires_at' => 'datetime',
            'email_confirmed_at' => 'datetime',
            'verified_at' => 'datetime',
            'confirmation_sent_at' => 'datetime',
            'confirmation_resend_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if (blank($order->public_id)) {
                $order->public_id = (string) Str::uuid();
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function repartidor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'repartidor_id');
    }

    public function issueCheckoutToken(): string
    {
        $token = Str::random(64);

        $this->forceFill([
            'checkout_token' => Hash::make($token),
        ])->saveQuietly();

        return $token;
    }

    public function hasValidCheckoutToken(?string $plainToken): bool
    {
        return filled($plainToken)
            && filled($this->checkout_token)
            && Hash::check($plainToken, $this->checkout_token);
    }
}
