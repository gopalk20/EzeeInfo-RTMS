<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run()
    {
        $teams = [
            ['name' => 'Default'],
        ];

        foreach ($teams as $team) {
            $existing = $this->db->table('teams')->where('name', $team['name'])->get()->getRow();
            if (!$existing) {
                $this->db->table('teams')->insert(array_merge($team, [
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]));
            }
        }
    }
}
