<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Backfills role_id and team_id for existing users after RoleSeeder and TeamSeeder.
 */
class UserBackfillSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        // Default role_id = 1 (Employee) for users without role
        if ($db->fieldExists('role_id', 'users')) {
            $db->query("UPDATE users SET role_id = 1 WHERE role_id IS NULL");
        }

        // Default team_id = 1 for users without team
        if ($db->fieldExists('team_id', 'users')) {
            $db->query("UPDATE users SET team_id = 1 WHERE team_id IS NULL");
        }

        // Backfill username from email if null
        if ($db->fieldExists('username', 'users') && $db->fieldExists('email', 'users')) {
            $db->query("UPDATE users SET username = email WHERE (username IS NULL OR username = '') AND email IS NOT NULL");
        }
    }
}
