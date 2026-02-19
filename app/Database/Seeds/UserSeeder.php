<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'     => 'Jane Smith',
                'email'    => 'jane@example.com',
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'     => 'Bob Johnson',
                'email'    => 'bob@example.com',
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
