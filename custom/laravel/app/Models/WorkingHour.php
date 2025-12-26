<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class WorkingHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'inbox_id',
        'day_of_week',
        'open_hour',
        'open_minutes',
        'close_hour',
        'close_minutes',
        'open_all_day',
        'closed_all_day',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'open_hour' => 'integer',
        'open_minutes' => 'integer',
        'close_hour' => 'integer',
        'close_minutes' => 'integer',
        'open_all_day' => 'boolean',
        'closed_all_day' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (WorkingHour $workingHour) {
            if (!$workingHour->account_id && $workingHour->inbox) {
                $workingHour->account_id = $workingHour->inbox->account_id;
            }

            // If open all day, set hours to 00:00 - 23:59
            if ($workingHour->open_all_day) {
                $workingHour->open_hour = 0;
                $workingHour->open_minutes = 0;
                $workingHour->close_hour = 23;
                $workingHour->close_minutes = 59;
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    /**
     * Check if currently open.
     */
    public function isOpenNow(): bool
    {
        if ($this->closed_all_day) {
            return false;
        }

        $timezone = $this->inbox->timezone ?? config('app.timezone');
        $now = Carbon::now($timezone);

        return $this->isOpenAt($now);
    }

    /**
     * Check if open at a given time.
     */
    public function isOpenAt(Carbon $time): bool
    {
        if ($this->closed_all_day) {
            return false;
        }

        $timezone = $this->inbox->timezone ?? config('app.timezone');
        $openTime = Carbon::now($timezone)->setTime($this->open_hour, $this->open_minutes);
        $closeTime = Carbon::now($timezone)->setTime($this->close_hour, $this->close_minutes);

        return $time->between($openTime, $closeTime);
    }

    /**
     * Check if currently closed.
     */
    public function isClosedNow(): bool
    {
        return !$this->isOpenNow();
    }
}
