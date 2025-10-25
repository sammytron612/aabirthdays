<?php

namespace App\Models;

use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'name',
        'role',
        'token',
        'expires_at',
        'accepted_at',
        'invited_by',
    ];

    protected $casts = [
        'role' => UserRole::class,
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isAccepted();
    }

    public function markAsAccepted(): void
    {
        $this->update(['accepted_at' => now()]);
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public static function createInvitation(string $email, string $name, UserRole $role, int $invitedBy): self
    {
        return self::create([
            'email' => $email,
            'name' => $name,
            'role' => $role,
            'token' => self::generateToken(),
            'expires_at' => Carbon::now()->addHours(48),
            'invited_by' => $invitedBy,
        ]);
    }
}
