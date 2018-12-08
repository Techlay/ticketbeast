<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTicketsTest extends TestCase
{
    /** @test */
    public function customer_can_purchase_concert_tickets()
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 3250]);

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $paymentGateway->getValidTestToken()
        ]);

        $this->assertEquals(9750, $paymentGateway->totalCharges());

        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets->count());
    }
}
