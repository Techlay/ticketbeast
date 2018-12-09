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

        $this->paymentGateway->charge(
            request('ticket_quantity') * $concert->ticket_price, request('payment_token')
        );

        $order = $concert->orderTickets(request('email'), request('ticket_quantity'));

        return response()->json([], 201);
    }
}
