<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'concert_id' => function () {
                return Concert::factory()->create()->id;
            },
           
        ];
    }

    public function reserved()
    {
        return $this->state([
            'reserved_at' => Carbon::now(),
        ]);
    }
}
