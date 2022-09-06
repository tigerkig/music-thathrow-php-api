<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            'Mastering',
            'Music Production',
            'Mixing',
        ];

        foreach ($services as $service) {
            $s = new Service();
            $s->name = $service;
            $s->save();
        }
    }
}
