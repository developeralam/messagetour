<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'orders',
            'bookings',
            'hotel',
            'aminities',
            'visa',
            'tour-package',
            'travel-product',
            'group-flight',
            'vehicle',
            'blog',
            'offer',
            'faq',
            'coporate-query',
            'payment-gateway',
            'commission',
            'coupon',
            'agent',
            'agent-report',
            'customer',
            'subscriber',
            'contactus',
            'reviews',
            'bank-payment',
            'voucher.list',
            'voucher.create',
            'voucher.edit',
            'voucher.delete',
            'other-transactions',
            'chart-of-account',
            'reports',
            'trial-balance',
            'balance-sheet',
            'deposit_request',
            'withdraw',
            'system-user-manage',
            'location',
            'globalsettings',
            'aboutus',
            'income-expense'
        ];
        foreach ($permissions as $permission) {
            $res = Permission::where('name', $permission)->first();
            if (empty($res)) {
                Permission::insert([
                    'guard_name' => 'web',
                    'name' => $permission,
                ]);
            }
        }
    }
}
