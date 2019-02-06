<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderConfirmationEmail;
use App\Order;
use Tests\TestCase;

class OrderConfirmationEmailTest extends TestCase
{
    /** @test */
    public function email_contains_a_link_to_the_order_confirmation_page()
    {
        $order = factory(Order::class)->make([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);
        $email = new OrderConfirmationEmail($order);
        $this->assertContains(url('/orders/ORDERCONFIRMATION1234'), $email->render());
    }

    /** @test */
    public function email_has_a_subject()
    {
        $order = factory(Order::class)->make();
        $email = new OrderConfirmationEmail($order);
        $this->assertEquals("Your TicketBeast Order", $email->build()->subject);
    }
}
