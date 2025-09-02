<?php

namespace Database\Seeders;

use App\Models\Aminities;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AminitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Ac',
            'Tv',
            'Telephone',
            'Towels',
            'Free Wi-Fi',
            'Wardrobe'
        ];
        foreach ($data as $value) {
            Aminities::create([
                'name' => $value
            ]);
        }
    }
}
