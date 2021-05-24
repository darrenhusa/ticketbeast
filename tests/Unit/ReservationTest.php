<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Reservation;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class ReservationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function calculating_the_total_cost()
    {
        $concert = Concert::factory()->create(['ticket_price' => 1200])->addTickets(3);
        $tickets = $concert->findTickets(3);

        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
      
    }
}
