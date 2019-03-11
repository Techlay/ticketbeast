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
    function can_get_total_charges_for_a_specific_account()
    {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken(), 'test_acct_0000');
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        $paymentGateway->charge(4000, $paymentGateway->getValidTestToken(), 'test_acct_1234');

        $this->assertEquals(6500, $paymentGateway->totalChargesFor('test_acct_1234'));
    }

    /** @test */
    public function running_a_hook_before_the_first_change()
    {
        $paymentGateway = new FakePaymentGateway;
        $timeCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$timeCallbackRan) {
            $timeCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_acct_1234');
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        $this->assertEquals(1, $timeCallbackRan);
        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }
}
