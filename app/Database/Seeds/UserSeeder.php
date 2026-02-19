<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'       => 'John Doe',
                'email'      => 'john@example.com',
                'password'   => password_hash('password123', PASSWORD_BCRYPT),
                'username'   => 'john',
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'role_id'    => 1,
                'team_id'    => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Jane Smith',
                'email'      => 'jane@example.com',
                'password'   => password_hash('password123', PASSWORD_BCRYPT),
                'username'   => 'jane',
                'first_name' => 'Jane',
                'last_name'  => 'Smith',
                'role_id'    => 2,
                'team_id'    => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Manager User',
                'email'      => 'manager@example.com',
                'password'   => password_hash('manager123', PASSWORD_BCRYPT),
                'username'   => 'manager',
                'first_name' => 'Manager',
                'last_name'  => 'User',
                'role_id'    => 3,
                'team_id'    => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Finance User',
                'email'      => 'finance@example.com',
                'password'   => password_hash('finance123', PASSWORD_BCRYPT),
                'username'   => 'finance',
                'first_name' => 'Finance',
                'last_name'  => 'User',
                'role_id'    => 4,
                'team_id'    => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Admin User',
                'email'      => 'admin@example.com',
                'password'   => password_hash('admin123', PASSWORD_BCRYPT),
                'username'   => 'admin',
                'first_name' => 'Admin',
                'last_name'  => 'User',
                'role_id'    => 5,
                'team_id'    => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($data as $row) {
            $existing = $this->db->table('users')->where('email', $row['email'])->get()->getRow();
            if (!$existing) {
                $this->db->table('users')->insert($row);
            } else {
                // Force update password so login always works with known credentials
                $this->db->table('users')->where('email', $row['email'])->update([
                    'password'   => $row['password'],
                    'role_id'    => $row['role_id'],
                    'team_id'    => $row['team_id'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
