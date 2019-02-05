<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function getPaymentGateway()
    {
        return new FakePaymentGateway;
    }

    /** @test */
    public function running_a_hook_before_the_first_change()
    {
        $paymentGateway = new FakePaymentGateway;
        $timeCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$timeCallbackRan) {
            $timeCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(1, $timeCallbackRan);
        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }
}
