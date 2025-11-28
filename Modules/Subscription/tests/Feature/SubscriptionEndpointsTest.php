<?php

namespace Modules\Subscription\tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Subscription\src\Models\Subscription;
use Tests\TestCase;

class SubscriptionEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_subscriptions()
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('subscription.index'))
            ->assertStatus(200)
            ->assertSee($subscription->id);
    }

    public function test_admin_can_view_all_subscriptions()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $subs = Subscription::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get(route('subscription.index'))
            ->assertStatus(200)
            ->assertSee($subs[0]->id)
            ->assertSee($subs[1]->id)
            ->assertSee($subs[2]->id);
    }

    public function test_user_cannot_view_others_subscription()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $subscription = Subscription::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->get(route('subscription.show', $subscription))
            ->assertStatus(403);
    }

    public function test_user_can_cancel_own_subscription()
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('subscription.cancel', $subscription))
            ->assertRedirect();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'cancelled',
        ]);
    }
}
