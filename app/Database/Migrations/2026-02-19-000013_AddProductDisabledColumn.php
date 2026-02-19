<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductDisabledColumn extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('is_disabled', 'products')) {
            $this->forge->addColumn('products', [
                'is_disabled' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('is_disabled', 'products')) {
            $this->forge->dropColumn('products', 'is_disabled');
        }
    }
}
