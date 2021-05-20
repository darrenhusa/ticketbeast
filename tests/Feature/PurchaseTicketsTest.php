<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse; 
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_purchase_concert_tickets()
    {
        $paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $paymentGateway);

        $concert = Concert::factory()->create(['ticket_price'  => 3250]);

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
           'email'  =>  'john@example.com',
           'ticket_quantity' =>  3, 
           'payment_token' =>  $paymentGateway->getValidTestToken(), 
        ]);
        
        // dd($response);

        // $response->assertStatus(201);

        $this->assertEquals(9750, $paymentGateway->totalCharges());

        $this->assertTrue($concert->orders->contains(function ($order) {
        return $order->email == 'john@exxample.com';
      }));


        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $this->assertEquals(3, $order->tickets->count());

    }
}
