<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed a sample product and task so employees can log time.
 */
class ProductTaskSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        // Get john (Employee) and jane (Product Lead) IDs
        $john = $db->table('users')->where('email', 'john@example.com')->get()->getRowArray();
        $jane = $db->table('users')->where('email', 'jane@example.com')->get()->getRowArray();

        if (!$john || !$jane) {
            return;
        }

        $johnId = (int) $john['id'];
        $janeId = (int) $jane['id'];

        // Create product "RTMS Phase 1"
        $existingProduct = $db->table('products')->where('name', 'RTMS Phase 1')->get()->getRow();
        if (!$existingProduct) {
            $db->table('products')->insert([
                'name'               => 'RTMS Phase 1',
                'start_date'         => date('Y-m-d'),
                'end_date'           => date('Y-m-d', strtotime('+3 months')),
                'max_allowed_hours'  => 200,
                'github_repo_url'    => 'https://github.com/codeigniter4/CodeIgniter4',
                'product_lead_id'    => $janeId,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);
            $productId = $db->insertID();
        } else {
            $productId = (int) $existingProduct->id;
            $db->table('products')->where('id', $productId)->update([
                'github_repo_url' => 'https://github.com/codeigniter4/CodeIgniter4',
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        // Create milestone
        $existingMs = $db->table('milestones')->where('product_id', $productId)->where('name', 'MVP Release')->get()->getRow();
        if (!$existingMs) {
            $db->table('milestones')->insert([
                'product_id'     => $productId,
                'name'           => 'MVP Release',
                'due_date'       => date('Y-m-d', strtotime('+2 months')),
                'release_status' => 'planned',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        // Add john and jane as product members
        foreach ([$johnId, $janeId] as $uid) {
            $exists = $db->table('product_members')->where(['product_id' => $productId, 'user_id' => $uid])->get()->getRow();
            if (!$exists) {
                $db->table('product_members')->insert([
                    'product_id'      => $productId,
                    'user_id'         => $uid,
                    'role_in_product' => 'member',
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Create task assigned to john
        $existingTask = $db->table('tasks')->where('title', 'Implement timesheet entry form')->get()->getRow();
        if (!$existingTask) {
            $db->table('tasks')->insert([
                'product_id'  => $productId,
                'title'       => 'Implement timesheet entry form',
                'status'      => 'In Progress',
                'assignee_id' => $johnId,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
