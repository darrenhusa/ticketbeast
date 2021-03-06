<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Reservation;
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
        $savedRequest = $this->app['request'];

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", $params);

        $this->app['request'] = $savedRequest;

        return $response;

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
        
        $concert = Concert::factory()->published()->create(['ticket_price'  => 3250])->addTickets(3);

        // dd($concert); -> works!

        $response = $this->orderTickets($concert, [
           'email'  =>  'john@example.com',
           'ticket_quantity' =>  3, 
           'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
        ]);
        
        // dd($response);

        $response->assertStatus(201);

        $response->assertJson([
            'email'  =>  'john@example.com',
            'ticket_quantity' =>  3, 
            'amount' =>  9750, 
         ]);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ordersFor('john@example.com')->first()->ticketQuantity());
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
            $concert = Concert::factory()->published()->create()->addTickets(3);
            
            $response = $this->orderTickets($concert, [
                'email' =>  'john@example.com', 
                'ticket_quantity' =>  3, 
                'payment_token' =>  'invalid-payment-token', 
             ]);
        
            $response->assertStatus(422);
            $this->assertFalse($concert->hasOrderFor('john@example.com'));
            $this->assertEquals(3, $concert->ticketsRemaining());
        }    

        /** @test */
        public function cannot_purchase_tickets_to_an_unpublished_concert()
        {
            $concert = Concert::factory()->unpublished()->create()->addTickets(3);

            $response = $this->orderTickets($concert, [
                'email' =>  'john@example.com', 
                'ticket_quantity' => 3,
                'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
             ]);

            $response->assertStatus(404);
            $this->assertFalse($concert->hasOrderFor('john@example.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        }

        /** @test */
        public function cannot_purchase_more_tickets_than_remain()
        {
            $concert = Concert::factory()->published()->create()->addTickets(50);
            
            $response = $this->orderTickets($concert, [
                'email' =>  'john@example.com', 
                'ticket_quantity' => 51,
                'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
             ]);

            $response->assertStatus(422);            
            $this->assertFalse($concert->hasOrderFor('john@example.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
            $this->assertEquals(50, $concert->ticketsRemaining());
        }
        
        /** @test */
        public function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase()
        {
            $concert = Concert::factory()->published()->create([
                'ticket_price' => 1200,
            ])->addTickets(3);

            $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {
            
                // $requestA = $this->app['request'];

                $response = $this->orderTickets($concert, [
                    'email' =>  'personB@example.com', 
                    'ticket_quantity' => 1,
                    'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
                 ]);

                // $this->app['request'] = $requestA;

                $response->assertStatus(422);            
                $this->assertFalse($concert->hasOrderFor('personB@example.com'));
                $this->assertEquals(0, $this->paymentGateway->totalCharges());
                    
            });

            $response = $this->orderTickets($concert, [
                'email' =>  'personA@example.com', 
                'ticket_quantity' => 3,
                'payment_token' =>  $this->paymentGateway->getValidTestToken(), 
             ]);

            //  dd($concert->orders()->first()->toArray());

             $this->assertEquals(3600, $this->paymentGateway->totalCharges());
             $this->assertTrue($concert->hasOrderFor('personA@example.com'));
             $this->assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
                 
        }
}
