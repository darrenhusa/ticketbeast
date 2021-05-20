<?php

namespace Tests\Unit;

use App\Models\Concert;
use Carbon\Carbon;
// use Database\Factories\ConcertFactory;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class ConcertTest extends TestCase
{
    // use RefreshDatabase;

    /** @test */
    public function can_get_formatted_date()
    {
    	$concert = Concert::factory()
    		->make([
    			'date' => Carbon::parse('2016-12-01 8:00pm'),
    	]);


		// dump($concert);
		// dd($concert);

    	// $date = $concert->formatted_date;


		$this->assertEquals('December 1, 2016', $concert->formatted_date);        
    }

	 /** @test */
	 public function can_get_formatted_start_time()
	 {
		 $concert = Concert::factory()
			 ->make([
				 'date' => Carbon::parse('2016-12-01 17:00'),
		 ]);
 
 
		 $this->assertEquals('5:00pm', $concert->formatted_start_time);        
	 }

	  /** @test */
	  public function can_get_ticket_price_in_dollars()
	  {
		  $concert = Concert::factory()
			  ->make([
				  'ticket_price' => 6750,
		  ]);
  
  
		  $this->assertEquals('67.50', $concert->ticket_price_in_dollars);        
	  }
}
