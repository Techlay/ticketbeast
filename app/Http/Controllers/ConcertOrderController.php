<?php

namespace App\Http\Controllers;

use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Http\Request;

class ConcertOrderController extends Controller
{
    private $paymentGateway;

    /**
     * ConcertOrderController constructor.
     * @param $paymentGateway
     */
    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store(int $concertId)
    {
        $concert = Concert::find($concertId);
        $ticketQuantity = request('ticket_quantity');
        $amount = $ticketQuantity * $concert->ticket_price;
        $token = request('payment_token');
        $this->paymentGateway->charge($amount, $token);
        return response()->json([], 201);
    }
}
