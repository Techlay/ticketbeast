<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
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
        $concert = Concert::published()->findOrFail($concertId);

        request()->validate([
            'email' => 'required|email',
            'ticket_quantity' => 'required|integer|min:1',
            'payment_token' => 'required',
        ]);

        try {
            $tickets = $concert->findTickets(request('ticket_quantity'));

            $this->paymentGateway->charge(
                request('ticket_quantity') * $concert->ticket_price, request('payment_token')
            );

            $order = Order::forTickets($tickets, request('email'));

            return response()->json($order, 201);

        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response([], 422);
        }
    }
}
