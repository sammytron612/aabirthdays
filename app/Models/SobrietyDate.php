<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SobrietyDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'sobriety_date',
    ];

    protected $casts = [
        'sobriety_date' => 'date',
    ];

    /**
     * Get the member that owns the sobriety date.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the number of days sober.
     */
    public function daysSober(): int
    {
        return $this->sobriety_date->diffInDays(now());
    }

    /**
     * Get the number of years sober.
     */
    public function yearsSober(): float
    {
        return round($this->daysSober() / 365.25, 1);
    }

    /**
     * Get a formatted string of time sober.
     */
    public function formattedTimeSober(): string
    {
        $days = $this->daysSober();
        $years = floor($days / 365.25);
        $months = floor(($days % 365.25) / 30.44);
        $remainingDays = floor($days % 30.44);

        $parts = [];
        if ($years > 0) $parts[] = $years . ' year' . ($years > 1 ? 's' : '');
        if ($months > 0) $parts[] = $months . ' month' . ($months > 1 ? 's' : '');
        if ($remainingDays > 0 || empty($parts)) $parts[] = $remainingDays . ' day' . ($remainingDays != 1 ? 's' : '');

        return implode(', ', $parts);
    }
}
