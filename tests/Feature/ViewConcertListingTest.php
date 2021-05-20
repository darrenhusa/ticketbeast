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
    public function user_can_view_a_published_concert_listing()
    {
        $concert = Concert::factory()->published()->create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'date'  => Carbon::parse('December 13, 2016 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city'  => 'Laraville',
            'state' => 'OR',
            'zip' => '17916',
            'additional_information'    => 'For tickets, call (555) 555-5555.',
        ]);

        // dd($concert);

        // $concert = Concert::create([
        //     'title' => 'The Red Chord',
        //     'subtitle' => 'with Animosity and Lethargy',
        //     'date'  => Carbon::parse('December 13, 2016 8:00pm'),
        //     'ticket_price' => 3250,
        //     'venue' => 'The Mosh Pit',
        //     'venue_address' => '123 Example Lane',
        //     'city'  => 'Laraville',
        //     'state' => 'OR',
        //     'zip' => '17916',
        //     'additional_information'    => 'For tickets, call (555) 555-5555.',
        //     'published_at'  => Carbon::parse('-1 week'),
        // ]);

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
     
        $concert = Concert::factory()->unpublished()->create();

        // $concert = Concert::factory()
        //         ->create([
        //     'published_at'  => null,
        // ]);


    // dd($concert);

    $response = $this->get('/concerts/'.$concert->id);
    
    // dd($response);

    $response->assertStatus(404);


   }
}
