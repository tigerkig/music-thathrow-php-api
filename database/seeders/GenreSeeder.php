<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Genre;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $genres = [
            'Gospel',
            'Trap',
            'Jazz',
            'Drill',
            'Afrobeat',
            'Reggae',
            'Hip Hop',
            'Dancehall',
            'R & B',
            'Grime',
        ];

        foreach ($genres as $genre) {
            Genre::create(
            [
                'name' => $genre,
            ]
            );
        }
    }
}
