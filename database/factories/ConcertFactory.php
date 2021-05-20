<?php

namespace Database\Factories;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConcertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Concert::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => 'Example Band',
            'subtitle' => 'with The Fake Openers',
            'date'  => Carbon::parse('+2 weeks'),
            'ticket_price' => 2000,
            'venue' => 'The Example Theatre',
            'venue_address' => '123 Example Lane',
            'city'  => 'Fakeville',
            'state' => 'OR',
            'zip' => '90210',
            'additional_information'    => 'Some sample additional information.'
        ];
    }

    public function published()
    {
        return $this->state([
            // 'published_at' => null,
            'published_at' => Carbon::parse('-1 week'),
        ]);
    }


    public function unpublished()
    {
        return $this->state([
            'published_at' => null,
        ]);
    }
    // public function published()
    // {
    //     return $this->state(function (array $attributes) {
    //         return [
    //             'published_at' => null,
    //         ];
    //     });
    // }

}
