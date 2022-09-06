<?php

namespace Database\Factories;

use App\Models\Beat;
use App\Models\BeatPurchase;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BeatPurchaseFactory extends Factory
{
    protected $model = BeatPurchase::class;

    public function definition(): array
    {
        return [
            'purchase_id' => Purchase::factory() ,
            'beat_id' => Beat::factory(),
            'price' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
