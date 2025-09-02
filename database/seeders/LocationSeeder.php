<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use App\Enum\CountryStatus;
use App\Enum\DistrictStatus;
use App\Enum\DivisionStatus;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $datas = [
            [
                'country' => 'Bangladesh',
                'regions' => [
                    'Dhaka' => ['Gazipur', 'Narayanganj'],
                    'Rangpur' => ['Kurigram', 'Lalmonirhat'],
                ],
            ],
            // Add more countries with their regions if needed
        ];

        foreach ($datas as $data) {
            $country = Country::create([
                'name' => $data['country'],
                'status' => CountryStatus::Active,
            ]);

            foreach ($data['regions'] as $divisionName => $districts) {
                $division = Division::create([
                    'country_id' => $country->id,
                    'name' => $divisionName,
                    'status' => DivisionStatus::Active,
                ]);

                foreach ($districts as $districtName) {
                    District::create([
                        'division_id' => $division->id,
                        'name' => $districtName,
                        'status' => DistrictStatus::Active,
                    ]);
                }
            }
        }
    }
}
