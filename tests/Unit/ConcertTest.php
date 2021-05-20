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
    use RefreshDatabase;

    /** @test */
    public function can_get_formatted_date()
    {
    	$concert = Concert::factory()
    		->create([
    			'date' => Carbon::parse('2016-12-01 8:00pm'),
    	]);


		dump($concert);
		// dd($concert);

    	$date = $concert->formatted_date;


		$this->assertEquals('December 1, 2016', $date);        
    }
}
