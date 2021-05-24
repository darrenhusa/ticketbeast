<?php

namespace Tests\Unit;

// use App\Models\Ticket;
use App\Reservation;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
// use Mockery;


class ReservationTest extends TestCase
{
    /** @test */
    public function calculating_the_total_cost()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
      
    }
}
