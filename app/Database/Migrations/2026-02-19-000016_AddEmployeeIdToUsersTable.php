<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmployeeIdToUsersTable extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('employee_id', 'users')) {
            $this->forge->addColumn('users', [
                'employee_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                    'after'      => 'last_name',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('employee_id', 'users')) {
            $this->forge->dropColumn('users', 'employee_id');
        }
    }
}
