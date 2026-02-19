<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'Employee'],
            ['name' => 'Product Lead'],
            ['name' => 'Manager'],
            ['name' => 'Finance'],
            ['name' => 'Super Admin'],
        ];

        foreach ($roles as $role) {
            $existing = $this->db->table('roles')->where('name', $role['name'])->get()->getRow();
            if (!$existing) {
                $this->db->table('roles')->insert(array_merge($role, [
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]));
            }
        }
    }
}
