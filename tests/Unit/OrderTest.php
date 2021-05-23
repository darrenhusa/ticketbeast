<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

   /** @test */
   public function tickets_are_released_when_an_order_is_cancelled()
   {
        $concert = Concert::factory()->create();
        // $concert = Concert::factory()->published()->create();
        $concert->addTickets(10);
    
        $order = $concert->orderTickets('jane@example.com', 5);
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertNull(Order::find($order->id));

   }
}