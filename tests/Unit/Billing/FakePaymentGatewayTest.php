<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use PHPUnit\Framework\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertEquals(2500, $paymentGateway->totalCharges());        

    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fail()
    {
        try {
            $paymentGateway = new FakePaymentGateway;

            $paymentGateway->charge(2500, 'invalid-payment-token');    
        } catch (PaymentFailedException $e) {
            return;
        }
        // $this->assertTrue(true);
        // $this->expectNotToPerformAssertions();
        $this->addToAssertionCount(1);
        $this->fail();
        
    }

    /** @test */
    public function running_a_hook_before_the_first_charge()
    {
        $paymentGateway = new FakePaymentGateway;

        $callbackRan = false;
        
        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$callbackRan) {
            $callbackRan = true;

            $this->assertEquals(0, $paymentGateway->totalCharges());
        }); 

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertTrue($callbackRan);
        $this->assertEquals(2500, $paymentGateway->totalCharges());

    }
}
