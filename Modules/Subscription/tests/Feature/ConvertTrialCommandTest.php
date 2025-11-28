<?php

namespace Modules\Subscription\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Modules\Subscription\src\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Modules\Subscription\src\Jobs\SendSubscriptionEmailJob;
use Carbon\Carbon;

class ConvertTrialCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_trials_are_converted_and_jobs_dispatched()
    {
        Queue::fake();

        $user = User::factory()->create();

        $expiredTrial = Subscription::factory()->expiredTrial()->create([
            'user_id' => $user->id,
        ]);

        $this->artisan('subscriptions:convert-trials')
            ->expectsOutput("Converted 1 expired trials to paid.")
            ->assertExitCode(0);

        $expiredTrial->refresh();

        $this->assertEquals('paid', $expiredTrial->type);
        $this->assertEquals('active', $expiredTrial->status);
        $this->assertNull($expiredTrial->ended_at);

        Queue::assertPushed(\App\Jobs\SendSubscriptionEmailJob::class, function ($job) use ($user) {
            return $job->user->id === $user->id;
        });
    }

    public function test_non_expired_trials_are_not_converted()
    {
        Queue::fake();

        $user = User::factory()->create();

        $activeTrial = Subscription::factory()->create([
            'user_id' => $user->id,
            'type' => 'trial',
            'status' => 'active',
            'trial_end_at' => Carbon::now()->addDay(),
        ]);

        $this->artisan('subscriptions:convert-trials')
            ->expectsOutput("Converted 0 expired trials to paid.")
            ->assertExitCode(0);

        $activeTrial->refresh();

        $this->assertEquals('trial', $activeTrial->type);

        Queue::assertNothingPushed();
    }
}
