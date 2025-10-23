<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'disabled',
    ];

    /**
     * Get the sobriety dates for the member.
     */
    public function sobrietyDates(): HasMany
    {
        return $this->hasMany(SobrietyDate::class);
    }

    /**
     * Get the most recent sobriety date for the member.
     */
    public function getMostRecentSobrietyDateAttribute()
    {
        return $this->sobrietyDates()->orderByDesc('sobriety_date')->first();
    }

    /**
     * Get the most recent sobriety date for the member (method version).
     */
    public function mostRecentSobrietyDate()
    {
        return $this->sobrietyDates()->orderByDesc('sobriety_date')->first();
    }
}
