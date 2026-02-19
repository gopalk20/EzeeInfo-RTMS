<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductType extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('product_type', 'products')) {
            $this->forge->addColumn('products', [
                'product_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'null'       => true,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('product_type', 'products')) {
            $this->forge->dropColumn('products', 'product_type');
        }
    }
}
