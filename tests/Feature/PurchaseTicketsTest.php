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

    protected function setUp():void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);

    }
    
    private function orderTickets($concert, $params)
    {
        return $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    private function assertValidationError($response, $field)
    {

        $response->assertStatus(422);
        $this->assertTrue($response[$field]);
        // $response->assertJson($field, $response[$field]);
        // $response->assertJson($response[$field], $response->decodeResponseJson());
        
        // $this->assertArrayHasKey($field, $response->decodeResponseJson());
        // dd($response->decodeResponseJson());
    }

    /** @test */
    public function customer_can_purchase_concert_tickets()
    {
        
        $concert = Concert::factory()->create(['ticket_price'  => 3250]);

        // dd($concert); -> works!

        $response = $this->orderTickets($concert, [
           'email'  =>  'john@example.com',
           'ticket_quantity' =>  3, 
           'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
        ]);
        
        // dd($response);

        $response->assertStatus(201);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);

        // $this->assertTrue($concert->orders->contains(function ($order) {
        //   return $order->email == 'john@exxample.com';
        // }));

        $this->assertEquals(3, $order->tickets()->count());

    }

    /** @test */
    public function email_is_required_to_purchase_tickets()
    {
        // $this->disableExceptionHandling();

        $concert = Concert::factory()->create();

        $response = $this->orderTickets($concert, [
            'ticket_quantity' =>  3, 
            'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
         ]);
        
         $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function email_must_be_valid_to_purchase_tickets()
    {
        $concert = Concert::factory()->create();

        $response = $this->orderTickets($concert, [
            'email' =>  'not-an-email-address', 
            'ticket_quantity' =>  3, 
            'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
         ]);
        
         $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = Concert::factory()->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' =>  'john@example.com', 
            'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
         ]);

         $this->assertValidationError($response, 'ticket_quantity');        
    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = Concert::factory()->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' =>  'john@example.com', 
            'ticket_quantity' => 0,
            'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
         ]);

         $this->assertValidationError($response, 'ticket_quantity');                
    }

        /** @test */
        public function payment_token_is_required_to_purchase_tickets()
        {
            $concert = Concert::factory()->create();
    
            $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
                'email' =>  'john@example.com', 
                'ticket_quantity' => 3,
             ]);

            $this->assertValidationError($response, 'payment_token');                
        }
    
}
