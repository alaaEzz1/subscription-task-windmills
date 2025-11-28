<?php

namespace Modules\Subscription\src\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Subscription\database\factories\SubscriptionFactory;



class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'plan_id',
        'type',
        'status',
        'trial_end_at',
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'trial_end_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return SubscriptionFactory::new();
    }

    protected static function booted()
    {
        static::creating(function ($subscription) {
            if ($subscription->type === 'trial' && !$subscription->trial_end_at) {
                $subscription->trial_end_at = Carbon::now()->addDays(7);
            }

            if (!$subscription->started_at) {
                $subscription->started_at = Carbon::now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // Helpers
    public function isTrial(): bool
    {
        return $this->type === 'trial';
    }

    public function isPaid(): bool
    {
        return $this->type === 'paid';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->trial_end_at && $this->trial_end_at->isPast());
    }

    // ======= Scopes =======
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->whereNotNull('trial_end_at')
                    ->where('trial_end_at', '<', now());
            });
    }

    public function scopeTrial($query)
    {
        return $query->where('type', 'trial');
    }

    public function scopePaid($query)
    {
        return $query->where('type', 'paid');
    }
}
