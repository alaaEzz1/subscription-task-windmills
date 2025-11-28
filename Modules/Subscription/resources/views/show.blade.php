@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Subscription #{{ $subscription->id }}</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <p><strong>User:</strong> {{ $subscription->user->name ?? '-' }}</p>
                <p><strong>Plan:</strong> {{ $subscription->plan->name ?? '-' }}</p>
                <p><strong>Type:</strong> {{ ucfirst($subscription->type) }}</p>
                <p><strong>Status:</strong> {{ ucfirst($subscription->status) }}</p>
                <p><strong>Started At:</strong> {{ optional($subscription->started_at)->format('Y-m-d') ?? '-' }}</p>
                <p><strong>Ended At:</strong> {{ optional($subscription->ended_at)->format('Y-m-d') ?? '-' }}</p>
                <p><strong>Trial Ends At:</strong> {{ optional($subscription->trial_end_at)->format('Y-m-d') ?? '-' }}</p>
            </div>
        </div>

        @can('cancel', $subscription)
            <form action="{{ route('subscription.cancel', $subscription->id) }}" method="POST" style="display:inline-block">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this subscription?')">
                    Cancel Subscription
                </button>
            </form>
        @endcan

        <a href="{{ route('subscription.index') }}" class="btn btn-secondary">Back to list</a>
    </div>
@endsection
