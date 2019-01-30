<?php

namespace Tests\Feature;

use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_their_order_confirmation()
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->create();
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);
        $ticket = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id
        ]);

        $response = $this->get('/orders/ORDERCONFIRMATION1234');

        $response->assertStatus(200);
        $response->assertViewHas('order', $order);
    }

    /** @test */
    public function retrieving_a_nonexistent_order_by_confirmation_number_throws_an_exception()
    {
        try {
            Order::findByConfirmationNumber('NONEXISTENTCONFIRMATIONNUMBER');
        } catch (ModelNotFoundException $e) {
            return;
        }

        $this->fail('No matching order was found for the specified confirmation number, but an exception was not thrown.');
    }
}
