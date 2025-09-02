<?php

namespace Database\Seeders;

use App\Models\Tour;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Visa;
use App\Models\Hotel;
use App\Models\Offer;
use App\Models\GroupFlight;
use App\Models\TravelProduct;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\AminitiesSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PaymentGatewaySeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(AminitiesSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(PaymentGatewaySeeder::class);
        $this->call(CountrySeeder::class);

        // Bulk Hotel Insert
        Hotel::factory()->count(50)->create();

        // Bulk Tour Insert
        Tour::factory()->count(50)->create();

        // Bulk TravelProduct Insert
        TravelProduct::factory()->count(50)->create();

        // Bulk Visa Insert
        Visa::factory()->count(50)->create();

        // Bulk Group Flight Insert
        GroupFlight::factory()->count(50)->create();

        // Bulk Offer Insert
        Offer::factory()->count(50)->create();
        // Seed payment gateway
    }
}
