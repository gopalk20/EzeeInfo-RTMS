<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ConfigSeeder extends Seeder
{
    public function run()
    {
        $configs = [
            ['key' => 'daily_hours_limit', 'value' => '24'],
            ['key' => 'd_plus_n_days', 'value' => '3'],
            ['key' => 'working_days', 'value' => '22'],
            ['key' => 'standard_hours', 'value' => '8'],
            ['key' => 'session_expiration', 'value' => '86400'], // 24h idle (FR-000a1)
        ];

        foreach ($configs as $config) {
            $existing = $this->db->table('config')->where('key', $config['key'])->get()->getRow();
            if (!$existing) {
                $this->db->table('config')->insert(array_merge($config, [
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]));
            }
        }
    }
}
