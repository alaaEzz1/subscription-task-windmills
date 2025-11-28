@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4 text-gray-800 text-3xl font-semibold">Subscriptions</h1>

        @if (session('success'))
            <div class="alert alert-success rounded-lg shadow-sm border-0 py-3 px-4 mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-0 overflow-x-auto">
                <table class="table table-hover mb-0">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="py-2 px-3">ID</th>
                            <th class="py-2 px-3">User</th>
                            <th class="py-2 px-3">Plan</th>
                            <th class="py-2 px-3">Type</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3">Started At</th>
                            <th class="py-2 px-3">Ended At</th>
                            <th class="py-2 px-3">Trial End</th>
                            <th class="py-2 px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $subscription)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3">{{ $subscription->id }}</td>
                                <td class="py-2 px-3">{{ $subscription->user->name ?? '-' }}</td>
                                <td class="py-2 px-3">{{ $subscription->plan->name ?? '-' }}</td>
                                <td class="py-2 px-3">{{ ucfirst($subscription->type) }}</td>
                                <td class="py-2 px-3">
                                    <span
                                        class="badge {{ $subscription->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </td>
                                <td class="py-2 px-3">{{ optional($subscription->started_at)->format('Y-m-d') ?? '-' }}</td>
                                <td class="py-2 px-3">{{ optional($subscription->ended_at)->format('Y-m-d') ?? '-' }}</td>
                                <td class="py-2 px-3">{{ optional($subscription->trial_end_at)->format('Y-m-d') ?? '-' }}
                                </td>
                                <td class="py-2 px-3">
                                    <a href="{{ route('subscription.show', $subscription->id) }}"
                                        class="btn btn-sm btn-primary me-1">View</a>

                                    @can('cancel', $subscription)
                                        <form action="{{ route('subscription.cancel', $subscription->id) }}" method="POST"
                                            style="display:inline-block">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Cancel this subscription?')">
                                                Cancel
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-gray-500">
                                    No subscriptions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $subscriptions->links() }}
        </div>
    </div>
@endsection
