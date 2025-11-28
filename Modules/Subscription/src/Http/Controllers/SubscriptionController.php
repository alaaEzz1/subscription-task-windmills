<?php

namespace Modules\Subscription\src\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Subscription\src\Models\Subscription;

class SubscriptionController extends Controller
{
    /**
     * Display list of subscriptions (Fixing N+1)
     */
    public function index()
    {
        // Admin → sees all
        // User → sees own only

        $query = Subscription::with(['user', 'plan'])->latest();

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        $subscriptions = $query->paginate(20);

        return view('Subscription::index', compact('subscriptions'));
    }

    /**
     * Show single subscription
     */
    public function show(Subscription $subscription)
    {
        // Policy check
        $this->authorize('view', $subscription);

        return view('Subscription::show', compact('subscription'));
    }

    /**
     * Create a new subscription
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'nullable|exists:plans,id',
            'type'    => 'required|in:trial,paid',
        ]);

        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'plan_id' => $validated['plan_id'] ?? null,
            'type'    => $validated['type'],
            'status'  => 'active',
        ]);

        return redirect()->route('subscription.show', $subscription->id)
            ->with('success', 'Subscription created successfully');
    }

    /**
     * Cancel a subscription
     */
    public function cancel(Subscription $subscription)
    {
        $this->authorize('cancel', $subscription);

        $subscription->update([
            'status' => 'cancelled',
            'ended_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Subscription cancelled.');
    }
}
