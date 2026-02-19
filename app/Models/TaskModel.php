<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table            = 'tasks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps   = true;
    protected $allowedFields   = ['product_id', 'milestone_id', 'github_issue_id', 'title', 'status', 'assignee_id', 'linked_branch', 'locked'];

    public function getByAssignee(int $userId): array
    {
        return $this->select('tasks.*, products.name as product_name')
            ->join('products', 'products.id = tasks.product_id')
            ->where('tasks.assignee_id', $userId)
            ->orderBy('tasks.created_at', 'DESC')
            ->findAll();
    }

    public function getByProduct(int $productId): array
    {
        return $this->where('product_id', $productId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
