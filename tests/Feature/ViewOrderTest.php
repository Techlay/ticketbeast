<?php

namespace Tests\Feature;

use App\Concert;
use App\Order;
use App\Ticket;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_their_order_confirmation()
    {
        $concert = factory(Concert::class)->create();
        $order = factory(Order::class)->create();
        $ticket = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id
        ]);

    }
}
