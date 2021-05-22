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

    private function assertValidationError($response, $field, $error_message)
    {
        $response->assertStatus(422);
        
        $response->assertJsonValidationErrors([
            $field => $error_message,
        ]);

        // $this->assertTrue($response[$field]);
        // $response->assertJson($field, $response[$field]);
        // $response->assertJson($response[$field], $response->decodeResponseJson());
        
        // $this->assertArrayHasKey($field, $response->decodeResponseJson());
        // dd($response->decodeResponseJson());
    }

    /** @test */
    public function customer_can_purchase_tickets_to_a_published_concert()
    {
        
        $concert = Concert::factory()->published()->create(['ticket_price'  => 3250]);

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

        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets($concert, [
            'ticket_quantity' =>  3, 
            'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
         ]);
        
        // dd($response->decodeResponseJson());
        // $responseAsArray = $response->decodeResponseJson();
        // dd($response->assertJson(['email']));

        // $response->assertJsonValidationErrors([
        //     'email' => 'The email field is required.',
        // ]);

        // dd($responseAsArray->assertJson(['email']));
        // dd($response->assertJson($response['email']));
        // dd($responseAsArray['email']);
        // dd($response->assertJson($responseAsArray['email']));

        //  dd($response['email']);
        //  dd($this->assertJson('email',));

        $error_message = "The email field is required.";
        $this->assertValidationError($response, 'email', $error_message);
    }

    /** @test */
    public function email_must_be_valid_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets($concert, [
            'email' =>  'not-an-email-address', 
            'ticket_quantity' =>  3, 
            'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
         ]);
        
        $error_message = "The email must be a valid email address.";
        $this->assertValidationError($response, 'email', $error_message);
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets($concert, [
            'email' =>  'john@example.com', 
            'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
         ]);

         $error_message = "The ticket quantity field is required.";
         $this->assertValidationError($response, 'ticket_quantity', $error_message);     
    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets($concert, [
            'email' =>  'john@example.com', 
            'ticket_quantity' => 0,
            'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
         ]);

         $error_message = "The ticket quantity must be at least 1";
         $this->assertValidationError($response, 'ticket_quantity', $error_message);                
    }

        /** @test */
        public function payment_token_is_required_to_purchase_tickets()
        {
            $concert = Concert::factory()->published()->create();
    
            $response = $this->orderTickets($concert, [
                'email' =>  'john@example.com', 
                'ticket_quantity' => 3,
             ]);
            
             $error_message = "The payment token field is required.";
            $this->assertValidationError($response, 'payment_token', $error_message);                
        }

        /** @test */
        public function an_order_is_not_created_if_payment_fails()
        {
            $concert = Concert::factory()->published()->create();
            
            $response = $this->orderTickets($concert, [
                'email' =>  'john@example.com', 
                'ticket_quantity' =>  3, 
                'payment_token' =>  'invalid-payment-token', 
             ]);
        
            $response->assertStatus(422);

            $order = $concert->orders()->where('email', 'john@example.com')->first();
            $this->assertNull($order);
        }    

        /** @test */
        public function cannot_purchase_tickets_to_an_unpublished_concert()
        {
            $concert = Concert::factory()->unpublished()->create();

            $response = $this->orderTickets($concert, [
                'email' =>  'john@example.com', 
                'ticket_quantity' => 3,
                'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
             ]);

            $response->assertStatus(404);
            $this->assertEquals(0, $concert->orders()->count());
            $this->assertEquals(0, $this->paymentGateway->totalCharges());

        }

        /** @test */
        public function cannot_purchase_more_tickets_than_remain()
        {
            $concert = Concert::factory()->published()->create();
            
            $concert->addTickets(50);

            $response = $this->orderTickets($concert, [
                'email' =>  'john@example.com', 
                'ticket_quantity' => 51,
                'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
             ]);

            $response->assertStatus(422);
            
            $order = $concert->orders()->where('email', 'john@example.com')->first();
            $this->assertNull($order);
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
            $this->assertEquals(50, $concert->ticketsRemaining());

        }
    
}
