<?php

namespace Modules\Subscription\database\factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Subscription\src\Models\Plan;
use Modules\Subscription\src\Models\Subscription;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['trial', 'paid']);
        $startedAt = Carbon::now()->subDays(rand(1, 5));

        return [
            'user_id'      => User::factory(),
            'plan_id'      => Plan::factory(),
            'type'         => $type,
            'status'       => 'active',
            'started_at'   => $startedAt,
            'trial_end_at' => $type === 'trial'
                ? Carbon::now()->addDays(7)
                : null,
            'ended_at'     => null,
        ];
    }

    // Trial expired specifically
    public function expiredTrial()
    {
        return $this->state(function () {
            return [
                'type'         => 'trial',
                'status'       => 'expired',
                'trial_end_at' => Carbon::now()->subDays(1),
            ];
        });
    }

    // Trial still active
    public function activeTrial()
    {
        return $this->state(function () {
            return [
                'type'         => 'trial',
                'trial_end_at' => Carbon::now()->addDays(3),
            ];
        });
    }

    // Paid subscription
    public function paid()
    {
        return $this->state(fn() => [
            'type' => 'paid',
            'trial_end_at' => null,
        ]);
    }
}
