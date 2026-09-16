<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('admin_settings', []) as $group => $settings) {
            foreach ($settings as $key => $value) {
                DB::table('dynamic_settings')->updateOrInsert(
                    ['setting_key' => $key],
                    [
                        'setting_value' => json_encode($value),
                        'setting_group' => $group,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}