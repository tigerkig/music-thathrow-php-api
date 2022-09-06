<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Part;

class PartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $parts = [
            'Kicks',
            'Piano',
            'Shaker',
            'Clap',
            'Hi Hat',
            'Vocal Sample',
            'FX',
            'Lead',
            'Guitar',
            'Bass',
            'Marimba',
        ];

        foreach ($parts as $part) {
            Part::create([
                'name' => $part,
            ]);
        }
    }
}
