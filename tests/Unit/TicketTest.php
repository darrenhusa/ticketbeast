<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use App\Models\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

     /** @test */
     public function a_ticket_can_be_reserved()
     {
        $ticket = Ticket::factory()->create();
         
        $ticket->reserve();

        
     }


    /** @test */
    public function a_ticket_can_be_released()
    {   
        $concert = Concert::factory()->create();
        $concert->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);
        $ticket = $order->tickets()->first();
        $this->assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);

    }

}
