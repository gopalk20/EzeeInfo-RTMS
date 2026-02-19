<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMilestonesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'release_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'default'    => 'planned',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('milestones');
    }

    public function down()
    {
        $this->forge->dropTable('milestones', true);
    }
}
