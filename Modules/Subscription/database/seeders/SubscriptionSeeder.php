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
        // 1️⃣ Users
        $users = User::factory()->count(50)->create();

        // 2️⃣ Plans
        $plans = collect(range(1, 5))->map(function ($i) {
            return Plan::create([
                'name' => "Plan {$i}",
                'price' => $i * 10,
                'duration_days' => 30,
            ]);
        });

        // 3️⃣ Subscriptions
        foreach ($users as $user) {
            foreach ($plans as $plan) {

                $rand = rand(1, 100);

                // 🟢 Paid Active (60%)
                if ($rand <= 60) {
                    Subscription::create([
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'type' => 'paid',
                        'status' => 'active',
                        'started_at' => Carbon::now()->subDays(rand(1, 20)),
                        'ended_at' => null,
                        'trial_end_at' => null,
                    ]);
                }

                // 🟡 Trial Active (20%)
                elseif ($rand <= 80) {
                    Subscription::create([
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'type' => 'trial',
                        'status' => 'active',
                        'started_at' => Carbon::now()->subDays(rand(1, 3)),
                        'trial_end_at' => Carbon::now()->addDays(rand(1, 5)),
                        'ended_at' => null,
                    ]);
                }

                // 🔴 Expired (20%)
                else {
                    $isTrial = rand(0, 1);

                    Subscription::create([
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'type' => $isTrial ? 'trial' : 'paid',
                        'status' => 'expired',
                        'started_at' => Carbon::now()->subDays(40),
                        'trial_end_at' => $isTrial ? Carbon::now()->subDays(30) : null,
                        'ended_at' => Carbon::now()->subDays(10),
                    ]);
                }
            }
        }
    }
}
