<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use App\Models\Order;
use App\Reservation;
use App\Billing\PaymentGateway;
use App\Billing\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;

    }

    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);
        
        $this->validate(request(), [
            'email' =>  ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'payment_token' =>  ['required'],
        ]);

        try {
            $reservation = $concert->reserveTickets(request('ticket_quantity'), request('email'));

            // dd(request()->all());

            $order = $reservation->complete($this->paymentGateway, request('payment_token'));

            return response()->json($order, 201);
        } catch (PaymentFailedException $e) {   
            $reservation->cancel();  
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {     
            return response()->json([], 422);
        }

        
    }
}
