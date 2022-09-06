<?php

namespace Database\Factories;

use App\Models\Beat;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BeatFactory extends Factory
{
    protected $model = Beat::class;

    public function definition(): array
    {

        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'price' => $this->faker->randomNumber(),
            'bpm' => $this->faker->randomNumber(),
            'is_free' => $this->faker->boolean(),
            'is_exclusive' => $this->faker->boolean(),
            'download_enabled' => $this->faker->boolean(),
            'purchase_enabled' => $this->faker->boolean(),
            'status' => $this->faker->randomElement([0,1,2,3,4]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }
}
