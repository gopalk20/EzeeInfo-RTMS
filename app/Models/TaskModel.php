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
        return $this->select('tasks.*, products.name as product_name, products.product_type, products.max_allowed_hours')
            ->join('products', 'products.id = tasks.product_id')
            ->groupStart()
                ->where('tasks.assignee_id', $userId)
                ->orWhere('products.product_type', 'leave')
            ->groupEnd()
            ->groupStart()
                ->where('products.is_disabled', null)
                ->orWhere('products.is_disabled', 0)
            ->groupEnd()
            ->orderBy('products.product_type', 'ASC')
            ->orderBy('products.name')
            ->orderBy('tasks.title')
            ->findAll();
    }

    public function getByProduct(int $productId): array
    {
        return $this->where('product_id', $productId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
