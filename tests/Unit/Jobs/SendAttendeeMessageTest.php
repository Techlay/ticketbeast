<?php

namespace Tests\Unit\Job;

use App\AttendeeMessage;
use App\Concert;
use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendAttendeeMessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_sends_the_message_to_all_concert_attendees()
    {
        Mail::fake();
        $concert = factory(Concert::class)->create();
        $concert->publish();
        $otherConcert = factory(Concert::class)->create();
        $otherConcert->publish();
        $message = AttendeeMessage::create([
            'concert_id' => $concert->id,
            'subject' => 'My subject',
            'message' => 'My message',
        ]);
        $orderA = $this->createForConcert($concert, ['email' => 'alex@example.com']);
        $otherOrder = $this->createForConcert($otherConcert, ['email' => 'jane@example.com']);
        $orderB = $this->createForConcert($concert, ['email' => 'sam@example.com']);
        $orderC = $this->createForConcert($concert, ['email' => 'taylor@example.com']);

        SendAttendeeMessage::dispatch($message);

        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('alex@example.com')
                && $mail->attendeeMessage->is($message);
        });
        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('sam@example.com')
                && $mail->attendeeMessage->is($message);
            ;
        });
        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('taylor@example.com')
                && $mail->attendeeMessage->is($message);
        });
        Mail::assertNotSent(AttendeeMessageEmail::class, function ($mail) {
            return $mail->hasTo('jane@example.com');
        });
    }
}
