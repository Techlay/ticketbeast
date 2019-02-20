<?php

namespace Tests\Feature\Backstage;

use App\AttendeeMessage;
use App\Concert;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageAttendeesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_promoter_can_view_the_message_form_for_their_own_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $concert->publish();

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.concert-messages.new');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function a_promoter_cannot_view_the_message_form_for_another_concert()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => factory(User::class)->create()]);
        $concert->publish();

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(404);
    }

    /** @test */
    function a_guest_cannot_view_the_message_form_for_any_concert()
    {
        $concert = factory(Concert::class)->create();
        $concert->publish();

        $response = $this->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertRedirect('/login');
    }

    /** @test */
    function a_promoter_can_send_a_new_message()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $concert->publish();

        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHas('flash');

        $message = AttendeeMessage::first();
        $this->assertEquals($concert->id, $message->concert_id);
        $this->assertEquals('My subject', $message->subject);
        $this->assertEquals('My message', $message->message);
    }

    /** @test */
    function a_promoter_cannot_send_a_new_message_for_other_concerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);
        $concert->publish();

        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, AttendeeMessage::count());
    }

    /** @test */
    function a_guest_cannot_send_a_new_message_for_any_concerts()
    {
        $concert = factory(Concert::class)->create();
        $concert->publish();

        $response = $this->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertRedirect('/login');
        $this->assertEquals(0, AttendeeMessage::count());
    }

    /** @test */
    function subject_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        $response = $this->from("/backstage/concerts/{$concert->id}/messages/new")
            ->actingAs($user)
            ->post("/backstage/concerts/{$concert->id}/messages", [
                'subject' => '',
                'message' => 'My message',
            ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHasErrors('subject');
        $this->assertEquals(0, AttendeeMessage::count());
    }

    /** @test */
    function message_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        $response = $this->from("/backstage/concerts/{$concert->id}/messages/new")
            ->actingAs($user)
            ->post("/backstage/concerts/{$concert->id}/messages", [
                'subject' => 'My subject',
                'message' => '',
            ]);
        
        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHasErrors('message');
        $this->assertEquals(0, AttendeeMessage::count());
    }
}
