<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use App\Models\WithdrawMethod;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'Bkash',
                'charge' => '1.5',
            ],
            [
                'name' => 'Rocket',
                'charge' => '1.5',
            ],
            [
                'name' => 'Ssl Commerze',
                'charge' => '1.5',
            ],
            [
                'name' => 'Cash On Delivery',
                'charge' => '1.5',
            ],
            [
                'name' => 'Wallet',
                'charge' => '0',
            ],
            [
                'name' => 'Manual',
                'charge' => '0',
            ],
        ];
        foreach ($gateways as $gateway) {
            PaymentGateway::create([
                'name' => $gateway['name'],
                'charge' => $gateway['charge'],
                'is_active' => true,
            ]);
            WithdrawMethod::create([
                'name' => $gateway['name'],
                'charge' => $gateway['charge'],
                'status' => true,
            ]);
        }
    }
}
