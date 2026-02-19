<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Create system products for leave types: Holiday, Sick Leave, Planned Leave, Training.
 * These are available for all users to log time.
 */
class LeaveProductsSeeder extends Seeder
{
    protected array $leaveTypes = ['Holiday', 'Sick Leave', 'Planned Leave', 'Training'];

    public function run()
    {
        $db = $this->db;
        foreach ($this->leaveTypes as $name) {
            $existing = $db->table('products')->where('name', $name)->where('product_type', 'leave')->get()->getRow();
            if ($existing) {
                continue;
            }
            $db->table('products')->insert([
                'name'         => $name,
                'product_type' => 'leave',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            $productId = (int) $db->insertID();
            $taskExists = $db->table('tasks')->where('product_id', $productId)->where('title', $name)->get()->getRow();
            if (! $taskExists) {
                $db->table('tasks')->insert([
                    'product_id' => $productId,
                    'title'      => $name,
                    'status'     => 'To Do',
                    'assignee_id'=> null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
