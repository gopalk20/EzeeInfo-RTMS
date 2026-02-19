<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDescriptionToTimeEntries extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('description', 'time_entries')) {
            $this->forge->addColumn('time_entries', [
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('description', 'time_entries')) {
            $this->forge->dropColumn('time_entries', 'description');
        }
    }
}
