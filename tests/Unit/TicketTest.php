<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use App\Models\Concert;
use App\Models\Ticket;
use Carbon\Carbon;
use App\Database\Factories\TicketFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

     /** @test */
     public function a_ticket_can_be_reserved()
     {
        $ticket = Ticket::factory()->create();
        $this->assertNull($ticket->reserved_at);
         
        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
     }


    /** @test */
    public function a_ticket_can_be_released()
    {   
        $ticket = Ticket::factory()->reserved()->create();
        
        $this->assertNotNull($ticket->reserved_at);
        
        $ticket->release();

        $this->assertNull($ticket->fresh()->reserved_at);
    }

}
