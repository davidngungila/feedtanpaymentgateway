<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Permission;
use App\Models\Provider;
use App\Models\Role;
use App\Payments\ProviderRegistry;
use App\Support\Security\Crypto;
use Illuminate\Database\Seeder;

class MobileMoneySeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProviderRegistry::codes() as $code) {
            $adapter = ProviderRegistry::get($code);
            Provider::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $adapter->name(),
                    'color' => $adapter->color(),
                    'driver' => config("mobile_money.providers.{$code}.driver", 'clickpesa'),
                    'is_active' => true,
                    'config' => [
                        'prefixes' => config("mobile_money.providers.{$code}.prefixes", []),
                        'webhook_secret_env' => config("mobile_money.providers.{$code}.webhook_secret_env"),
                    ],
                ]
            );
        }

        $roles = [
            'admin' => ['Administrator', 'Full access to providers, credentials, settlements and system configuration.'],
            'supervisor' => ['Supervisor', 'Reviews unmatched items, approves refunds and reconciliations. No credential or settings access.'],
            'cashier' => ['Cashier', 'Initiates collections and views masked customer data. No approvals, no credentials.'],
        ];
        foreach ($roles as $code => [$name, $desc]) {
            Role::updateOrCreate(['code' => $code], ['name' => $name, 'description' => $desc]);
        }

        foreach (Permission::catalogue() as $code => $desc) {
            Permission::updateOrCreate(['code' => $code], ['name' => ucwords(str_replace(['.', '_'], ' ', $code)), 'description' => $desc]);
        }

        foreach (Permission::defaults() as $roleCode => $codes) {
            $role = Role::where('code', $roleCode)->first();
            if (! $role) {
                continue;
            }
            $ids = Permission::whereIn('code', $codes)->pluck('id')->all();
            $role->permissions()->sync($ids);
        }

        $samples = [
            ['Amina Juma', '255750000001', 'amina.juma@example.co.tz'],
            ['Baraka Mwenda', '255680000002', 'baraka.mwenda@example.co.tz'],
            ['Chiku Said', '255650000003', 'chiku.said@example.co.tz'],
            ['Doto Mashaka', '255620000004', 'doto.mashaka@example.co.tz'],
            ['Elisha Mrope', '255730000005', 'elisha.mrope@example.co.tz'],
        ];
        foreach ($samples as [$name, $phone, $email]) {
            if (Customer::findByPhone($phone)) {
                continue;
            }
            Customer::create([
                'name' => $name,
                'phone_encrypted' => $phone,
                'phone_hash' => Crypto::blindIndex('customer', $phone),
                'email_encrypted' => $email,
                'email_hash' => Crypto::blindIndex('customer', $email),
                'status' => 'active',
            ]);
        }
    }
}
