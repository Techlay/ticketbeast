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

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published-concert-orders.index');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function a_promoter_can_view_the_10_most_recent_orders_for_their_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $concert->publish();

        $oldOrder = $this->createForConcert($concert, ['created_at' => Carbon::parse('11 days ago')]);
        $recentOrder1 = $this->createForConcert($concert, ['created_at' => Carbon::parse('10 days ago')]);
        $recentOrder2 = $this->createForConcert($concert, ['created_at' => Carbon::parse('9 days ago')]);
        $recentOrder3 = $this->createForConcert($concert, ['created_at' => Carbon::parse('8 days ago')]);
        $recentOrder4 = $this->createForConcert($concert, ['created_at' => Carbon::parse('7 days ago')]);
        $recentOrder5 = $this->createForConcert($concert, ['created_at' => Carbon::parse('6 days ago')]);
        $recentOrder6 = $this->createForConcert($concert, ['created_at' => Carbon::parse('5 days ago')]);
        $recentOrder7 = $this->createForConcert($concert, ['created_at' => Carbon::parse('4 days ago')]);
        $recentOrder8 = $this->createForConcert($concert, ['created_at' => Carbon::parse('3 days ago')]);
        $recentOrder9 = $this->createForConcert($concert, ['created_at' => Carbon::parse('2 days ago')]);
        $recentOrder10 = $this->createForConcert($concert, ['created_at' => Carbon::parse('1 days ago')]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->data('orders')->assertNotContains($oldOrder);
        $response->data('orders')->assertEquals([
            $recentOrder10,
            $recentOrder9,
            $recentOrder8,
            $recentOrder7,
            $recentOrder6,
            $recentOrder5,
            $recentOrder4,
            $recentOrder3,
            $recentOrder2,
            $recentOrder1,
        ]);
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
