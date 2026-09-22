<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_repartidor',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'is_repartidor' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'repartidor' => $this->is_repartidor,
            default => $this->is_admin,
        };
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Order::class, 'repartidor_id');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $initial = mb_strtoupper(mb_substr($this->name ?? 'U', 0, 1));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">'
            .'<circle cx="20" cy="20" r="20" fill="#1fb86a"/>'
            .'<text x="20" y="26" font-size="18" font-family="sans-serif" font-weight="700" text-anchor="middle" fill="#ffffff">'
            .htmlspecialchars($initial, ENT_XML1)
            .'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
