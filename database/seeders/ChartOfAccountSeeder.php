<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $accounts = [
            // ASSET
            ['code' => '101', 'name' => 'Cash', 'type' => 'asset'],
            ['code' => '102', 'name' => 'Bank', 'type' => 'asset'],
            // ['code' => '103', 'name' => 'Inventory - Vehicles', 'type' => 'asset'],
            // ['code' => '104', 'name' => 'Inventory - Spare Parts', 'type' => 'asset'],
            // ['code' => '105', 'name' => 'Accounts Receivable (Customer)', 'type' => 'asset'],
            // ['code' => '106', 'name' => 'Commission Receivables (Dealer Commission)', 'type' => 'asset'],

            // LIABILITY
            // ['code' => '201', 'name' => 'Accounts Payable (Supplier/Vendor)', 'type' => 'liability'],
            // ['code' => '202', 'name' => 'LC Payable', 'type' => 'liability'],
            // ['code' => '203', 'name' => 'Vendor Invoice Payable', 'type' => 'liability'],
            // ['code' => '204', 'name' => 'Capital Account', 'type' => 'liability'],
            // ['code' => '205', 'name' => 'Tax Payable Account', 'type' => 'liability'],
            // ['code' => '206', 'name' => 'Accounts Payable (Direct Cost)', 'type' => 'liability'],
            // ['code' => '207', 'name' => 'Accounts Payable (Billable Cost)', 'type' => 'liability'],
            // ['code' => '208', 'name' => 'Accounts Payable (Customer-Part Exchange)', 'type' => 'liability'],

            // REVENUE
            // ['code' => '301', 'name' => 'Discount Commission Income', 'type' => 'revenue'],
            // ['code' => '302', 'name' => 'Vehicle Sales Account', 'type' => 'revenue'],
            // ['code' => '303', 'name' => 'Spare Parts Sales Account', 'type' => 'revenue'],
            // ['code' => '304', 'name' => 'Vehicle Sales Return Account', 'type' => 'revenue'],
            // ['code' => '305', 'name' => 'Spare Parts Sales Return Account', 'type' => 'revenue'],

            // EXPENSE
            // ['code' => '401', 'name' => 'Discount Commissions Expense', 'type' => 'expense'],

            // PURCHASE & SALES
            // ['code' => '501', 'name' => 'Vehicle Purchase Account', 'type' => 'expense'],
            // ['code' => '502', 'name' => 'Spare Parts Purchase Account', 'type' => 'expense'],
            // ['code' => '503', 'name' => 'Vehicle Purchase Return Account', 'type' => 'expense'],
            // ['code' => '504', 'name' => 'Spare Parts Purchase Return Account', 'type' => 'expense'],
        ];

        foreach ($accounts as $acc) {
            ChartOfAccount::firstOrCreate([
                'code' => $acc['code'],
                'name' => $acc['name'],
                'type' => $acc['type'],
            ]);
        }
    }
}
