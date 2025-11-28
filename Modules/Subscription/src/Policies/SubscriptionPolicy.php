<?php

namespace Modules\Subscription\src\Policies;

use App\Models\User;
use Modules\Subscription\src\Models\Subscription;

class SubscriptionPolicy
{
    public function before(User $user)
    {
        if ($user->role === 'admin') {
            return true;
        }
    }

    public function view(User $user, Subscription $subscription)
    {
        return $user->role === 'admin' || $subscription->user_id === $user->id;
    }

    public function cancel(User $user, Subscription $subscription)
    {
        return $subscription->user_id === $user->id;
    }

    public function delete(User $user, Subscription $subscription)
    {
        return $subscription->user_id === $user->id;
    }
}
