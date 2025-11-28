<?php

namespace Modules\Subscription\tests\Unit;

use App\Jobs\SendSubscriptionEmailJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendSubscriptionEmailJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_email()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $job = new SendSubscriptionEmailJob(
            $user,
            'Test Subject',
            'Hello World'
        );

        $job->handle();

        Mail::assertSent(\App\Mail\SubscriptionEmail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->subjectText === 'Test Subject'
                && str_contains($mail->bodyText, 'Hello World');
        });
    }
}
