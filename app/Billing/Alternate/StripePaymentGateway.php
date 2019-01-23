<?php


namespace App\Billing\Alternate;


use App\Billing\PaymentGateway;

class StripePaymentGateway implements PaymentGateway
{
    private $stripeClient;

    public function __construct(\Stripe\ApiClient $stripeClient)
    {
        $this->apiClient = $apiClient;
    }

    public function charge($amount, $token)
    {
        $this->stripeClient->createCharge([
            'amount' => $amount,
            'source' => $token,
            'currency' => 'aud',
        ]);
    }
}
