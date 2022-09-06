<?php

namespace Database\Factories;

use App\Models\Beat;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class UploadFactory extends Factory
{
    protected $model = Upload::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'file_type' => $this->faker->word(),
            'file_size' => $this->faker->randomNumber(),
            'name' => $this->faker->name(),
            'url' => $this->faker->imageUrl(),
            'public' => $this->faker->boolean(),
            'deleted_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'beat_id' => Beat::factory(),
        ];
    }
}
