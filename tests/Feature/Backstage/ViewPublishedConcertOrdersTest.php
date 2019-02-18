<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\Order;
use App\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewPublishedConcertOrdersTest extends TestCase
{
    use RefreshDatabase;

    private function createForConcert($concert, $overrides = [], $ticketQuantity = 1)
    {
        $order = factory(Order::class)->create($overrides);
        $tickets = factory(Ticket::class, $ticketQuantity)->create(['concert_id' => $concert->id]);
        $order->tickets()->saveMany($tickets);
        return $order;
    }

    /** @test */
    function a_promoter_can_view_the_orders_of_their_own_published_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $concert->publish();
        $order = $this->createForConcert($concert, ['created_at' => Carbon::parse('11 days ago')]);
        dd($order);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published-concert-orders.index');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function a_promoter_cannot_view_the_orders_of_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    function a_promoter_cannot_view_the_orders_of_another_published_concert()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    function a_guest_cannot_view_the_orders_of_any_published_concert()
    {
        $concert = factory(Concert::class)->create();
        $concert->publish();

        $response = $this->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertRedirect('/login');
    }
}
