<?php

namespace App\Models;

use CodeIgniter\Model;

class MilestoneModel extends Model
{
    protected $table            = 'milestones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps   = true;
    protected $allowedFields   = ['product_id', 'name', 'due_date', 'release_status'];

    public function getByProduct(int $productId): array
    {
        return $this->where('product_id', $productId)
            ->orderBy('due_date', 'ASC')
            ->findAll();
    }
}
