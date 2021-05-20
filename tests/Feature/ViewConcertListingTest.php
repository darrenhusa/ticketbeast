<?php

namespace Tests\Feature;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class ViewConcertListingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_a_concert_listing()
    {
        $concert = Concert::create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'date'  => Carbon::parse('December 13, 2016 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city'  => 'Laraville',
            'state' => 'OR',
            'zip' => '17916',
            'additional_information'    => 'For tickets, call (555) 555-5555.'
        ]);

        // $response = $this->get('/concerts/'.$concert->id);
        
        // $response->assertSee('The Red Chord');
        // $response->assertSee('The Red Chord');
        // $response->assertSee('with Animosity and Lethargy');
        // $response->assertSee('December 13, 2016');
        // $response->assertSee('8:00pm');
        // $response->assertSee('32.50');
        // $response->assertSee('The Mosh Pit');
        // $response->assertSee('123 Example Lane');
        // $response->assertSee('Laraville, OR 1?7916');
        // $response->assertSee('For tickets, call (555) 555-5555.');

        $view = $this->view('concerts.show', ['concert' => $concert]);

        $view->assertSee('The Red Chord');
        $view->assertSee('with Animosity and Lethargy');
        $view->assertSee('December 13, 2016');
        $view->assertSee('8:00pm');
        $view->assertSee('32.50');
        $view->assertSee('The Mosh Pit');
        $view->assertSee('123 Example Lane');
        $view->assertSee('Laraville, OR 17916');
        $view->assertSee('For tickets, call (555) 555-5555.');
    }

   /** @test */
   public function user_cannot_view_unpublished_concert_listings()
   {
     
        $concert = Concert::factory()
                ->create([
            'published_at'  => null,
        ]);

    // dd($concert);

    $response = $this->get('/concerts/'.$concert->id);
    
    $response->assertStatus(404);


   }
}
