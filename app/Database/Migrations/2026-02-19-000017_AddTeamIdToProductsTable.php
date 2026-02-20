<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTeamIdToProductsTable extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('team_id', 'products')) {
            $this->forge->addColumn('products', [
                'team_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'product_lead_id',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('team_id', 'products')) {
            $this->forge->dropColumn('products', 'team_id');
        }
    }
}
