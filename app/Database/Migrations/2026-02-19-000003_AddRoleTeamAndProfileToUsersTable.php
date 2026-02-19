<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleTeamAndProfileToUsersTable extends Migration
{
    public function up()
    {
        $fields = [
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'team_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => true,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
            ],
        ];

        foreach ($fields as $name => $field) {
            if (!$this->db->fieldExists($name, 'users')) {
                $this->forge->addColumn('users', [$name => $field]);
            }
        }

        // Backfill: first_name from name (role_id/team_id set by UserBackfillSeeder after RoleSeeder)
        if ($this->db->fieldExists('name', 'users') && $this->db->fieldExists('first_name', 'users')) {
            $this->db->query('UPDATE users SET first_name = COALESCE(first_name, name), last_name = COALESCE(last_name, "") WHERE first_name IS NULL');
        }
    }

    public function down()
    {
        $columns = ['phone', 'last_name', 'first_name', 'username', 'team_id', 'role_id'];
        foreach ($columns as $col) {
            if ($this->db->fieldExists($col, 'users')) {
                $this->forge->dropColumn('users', $col);
            }
        }
    }
}
