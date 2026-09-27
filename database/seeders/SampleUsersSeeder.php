<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    /**
     * Sample sign-in accounts (password: password).
     */
    public function run(): void
    {
        $samples = [
            ['name' => 'Zawadi Mushi', 'email' => 'zawadi@moneyagent.local', 'phone' => '255755000101', 'role' => 'admin', 'agent_id' => null],
            ['name' => 'Neema Kimaro', 'email' => 'neema@moneyagent.local', 'phone' => '255756000102', 'role' => 'supervisor', 'agent_id' => null],
            ['name' => 'Juma Mwanza', 'email' => 'juma@moneyagent.local', 'phone' => '255687000103', 'role' => 'cashier', 'agent_id' => null],
            ['name' => 'Amina Juma', 'email' => 'amina@moneyagent.local', 'phone' => '255653000104', 'role' => 'cashier', 'agent_id' => null],
        ];

        foreach ($samples as $sample) {
            User::updateOrCreate(
                ['email' => $sample['email']],
                $sample + ['password' => Hash::make('password'), 'is_active' => true]
            );
        }
    }
}
