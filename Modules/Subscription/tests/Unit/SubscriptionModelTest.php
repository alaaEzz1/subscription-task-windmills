<?php

namespace Modules\Subscription\tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Subscription\src\Models\Subscription;
use Tests\TestCase;

class SubscriptionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_trial_and_is_paid_helpers()
    {
        $subscription = Subscription::factory()->create(['type' => 'trial']);
        $this->assertTrue($subscription->isTrial());
        $this->assertFalse($subscription->isPaid());

        $subscription->type = 'paid';
        $this->assertTrue($subscription->isPaid());
        $this->assertFalse($subscription->isTrial());
    }

    public function test_is_active_and_is_expired_helpers()
    {
        $subscription = Subscription::factory()->create(['status' => 'active']);
        $this->assertTrue($subscription->isActive());
        $this->assertFalse($subscription->isExpired());

        $subscription->status = 'expired';
        $this->assertTrue($subscription->isExpired());
    }

    public function test_scopes()
    {
        Subscription::factory()->count(2)->create(['type' => 'trial', 'status' => 'active']);
        Subscription::factory()->count(3)->create(['type' => 'paid', 'status' => 'active']);
        Subscription::factory()->count(1)->expiredTrial()->create();

        $this->assertCount(3, Subscription::trial()->get());
        $this->assertCount(3, Subscription::paid()->get());
        $this->assertCount(1, Subscription::expired()->get());
    }
}
