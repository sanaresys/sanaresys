<?php

namespace Database\Seeders;

use App\Models\BillingModule;
use Illuminate\Database\Seeder;

class BillingModulesSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = (array) config('billing.modules', []);

        if ($defaults === []) {
            $defaults = [
                [
                    'code' => 'nomina',
                    'name' => 'Modulo de Nomina',
                    'description' => 'Gestion de nomina medica para la clinica.',
                    'price_monthly' => 29.90,
                    'currency' => 'USD',
                    'is_active' => true,
                ],
            ];
        }

        foreach ($defaults as $module) {
            if (! is_array($module) || empty($module['code'])) {
                continue;
            }

            BillingModule::query()->updateOrCreate(
                ['code' => (string) $module['code']],
                [
                    'name' => (string) ($module['name'] ?? strtoupper((string) $module['code'])),
                    'description' => (string) ($module['description'] ?? ''),
                    'price_monthly' => (float) ($module['price_monthly'] ?? 0),
                    'currency' => (string) ($module['currency'] ?? config('billing.currency', 'USD')),
                    'is_active' => (bool) ($module['is_active'] ?? true),
                    'meta' => (array) ($module['meta'] ?? []),
                ],
            );
        }
    }
}

