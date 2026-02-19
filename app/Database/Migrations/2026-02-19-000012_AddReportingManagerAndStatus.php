<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReportingManagerAndStatus extends Migration
{
    public function up()
    {
        // users: reporting_manager_id, is_active
        if (!$this->db->fieldExists('reporting_manager_id', 'users')) {
            $this->forge->addColumn('users', [
                'reporting_manager_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
        }
        if (!$this->db->fieldExists('is_active', 'users')) {
            $this->forge->addColumn('users', [
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
            ]);
        }
        // Add FK if column exists and FK not present
        if ($this->db->fieldExists('reporting_manager_id', 'users')) {
            $fkExists = false;
            $fks = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'reporting_manager_id'")->getResultArray();
            if (!empty($fks)) {
                $fkExists = true;
            }
            if (!$fkExists) {
                $this->forge->addForeignKey('reporting_manager_id', 'users', 'id', 'SET NULL', 'SET NULL', 'users_reporting_manager_fk');
            }
        }

        // time_entries: status (pending_approval, approved)
        if (!$this->db->fieldExists('status', 'time_entries')) {
            $this->forge->addColumn('time_entries', [
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'default'    => 'pending_approval',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('reporting_manager_id', 'users')) {
            try {
                $this->forge->dropForeignKey('users', 'users_reporting_manager_fk');
            } catch (\Throwable $e) {
                // FK may have different name
            }
            $this->forge->dropColumn('users', ['reporting_manager_id', 'is_active']);
        }
        if ($this->db->fieldExists('status', 'time_entries')) {
            $this->forge->dropColumn('time_entries', 'status');
        }
    }
}
