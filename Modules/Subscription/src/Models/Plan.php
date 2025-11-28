<?php

namespace Modules\Subscription\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Subscription\database\factories\PlanFactory;


class Plan extends Model
{

    use HasFactory;
    protected $fillable = ['name', 'price', 'duration_days'];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    protected static function newFactory()
    {
        return PlanFactory::new();
    }
}
