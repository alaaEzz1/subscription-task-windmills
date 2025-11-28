<?php

namespace Modules\Subscription\database\seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;
use Modules\Subscription\src\Models\Plan;
use Modules\Subscription\src\Models\Subscription;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@alaaezz.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $plan = Plan::first() ?? Plan::create([
            'name' => 'Test Plan',
            'price' => 10,
            'duration_days' => 30,
        ]);

        $user = User::first() ?? User::factory()->create();

        // 1️⃣ Trial expired
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'type' => 'trial',
            'status' => 'active',
            'trial_end_at' => Carbon::now()->subMinutes(1),
            'started_at' => Carbon::now()->subDays(7),
        ]);

        // 2️⃣ Trial active
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'type' => 'trial',
            'status' => 'active',
            'trial_end_at' => Carbon::now()->addDays(3),
            'started_at' => Carbon::now(),
        ]);

        // 3️⃣ Paid active
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'type' => 'paid',
            'status' => 'active',
            'trial_end_at' => null,
            'started_at' => Carbon::now()->subDays(10),
            'ended_at' => null,
        ]);

        // 4️⃣ Paid expired
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'type' => 'paid',
            'status' => 'expired',
            'trial_end_at' => null,
            'started_at' => Carbon::now()->subDays(30),
            'ended_at' => Carbon::now()->subDays(1),
        ]);
    }
}
