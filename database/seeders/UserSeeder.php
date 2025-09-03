<?php

namespace Database\Seeders;

use App\Enum\AgentStatus;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Agent;
use App\Enum\UserType;
use App\Enum\AgentType;
use App\Enum\UserStatus;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::create([
            'name' => 'Super Admin'
        ]);
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@massagetourtravels.com',
            'password' => Hash::make('12345678'),
            'type' => UserType::Admin,
            'status' => UserStatus::Active
        ]);
        $user->assignRole($role);
        $customer = User::create([
            'name' => 'Md Alam',
            'email' => 'mdalam7246@gmail.com',
            'password' => Hash::make('12345678'),
            'status' => UserStatus::Active,
            'type' => UserType::Customer,
        ]);

        Customer::create([
            'user_id' => $customer->id
        ]);

        // $agent = User::create([
        //     'name' => 'Md Emon',
        //     'email' => 'usskyjraks@gmail.com',
        //     'password' => Hash::make('12345678'),
        //     'status' => UserStatus::Active,
        //     'type' => UserType::Agent,
        // ]);

        // Agent::create([
        //     'user_id' => $agent->id,
        //     'agent_type' => AgentType::General,
        //     'business_name' => 'Autopilot',
        //     'business_email' => 'autopilot@flyvaly.com',
        //     'business_address' => 'BOF, Gazipur, Dhaka, Bangladesh',
        //     'propiter_nid' => '3264843744',
        //     'propiter_etin_no' => '001',
        //     'zipcode' => '1703',
        //     'validity' => Carbon::now()->addYear(),
        //     'status' => AgentStatus::Approve
        // ]);
    }
}
