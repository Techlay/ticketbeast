<?php

namespace App\Billing;

use Stripe\Charge;
use Stripe\Error\InvalidRequest;
use Stripe\Token;

class StripePaymentGateway implements PaymentGateway
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge($amount, $token)
    {
        try {
            Charge::create([
                'amount' => $amount,
                'source' => $token,
                'currency' => 'aud'
            ], ['api_key' => $this->apiKey]);
        } catch (InvalidRequest $e) {
            return new PaymentFailedException;
        }
    }

    public function getValidTestToken()
    {
        return Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123'
            ]
        ], ['api_key' => $this->apiKey])->id;
    }
}
