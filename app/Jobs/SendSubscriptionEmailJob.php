<?php

namespace App\Jobs;

use App\Mail\SubscriptionEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public $user,
        public string $subject,
        public string $body
    ) {}

    public function handle()
    {
        Mail::to($this->user->email)
            ->send(new SubscriptionEmail($this->subject, $this->body));
    }
}
