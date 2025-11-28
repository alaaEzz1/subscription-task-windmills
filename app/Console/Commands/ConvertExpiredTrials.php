<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Subscription\src\Models\Subscription;
use App\Jobs\SendSubscriptionEmailJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ConvertExpiredTrials extends Command
{
    protected $signature = 'subscriptions:convert-trials';
    protected $description = 'Convert expired trial subscriptions to paid and send email notification';

    public function handle()
    {
        Log::info('ConvertExpiredTrials command executed at ' . now());

        // جلب كل الاشتراكات التجريبية التي انتهت بغض النظر عن الحالة
        $expiredTrials = Subscription::trial()
            ->where('trial_end_at', '<', Carbon::now())
            ->get();

        foreach ($expiredTrials as $subscription) {
            $subscription->type = 'paid';
            $subscription->status = 'active';
            $subscription->started_at = Carbon::now();
            $subscription->ended_at = null;
            $subscription->save();

            // Dispatch email job
            SendSubscriptionEmailJob::dispatch(
                $subscription->user,
                'Your trial has been converted',
                'Hello, your trial subscription has expired and is now converted to paid.'
            );
        }

        $this->info("Converted {$expiredTrials->count()} expired trials to paid.");
        Log::info("Converted {$expiredTrials->count()} expired trials to paid.");
    }
}
