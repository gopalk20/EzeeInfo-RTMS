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

    /**
     * Tasks for task portal: from products user has access to (FR-008b).
     * Includes assigned, unassigned, and GitHub-synced tasks. Super Admin sees all.
     */
    public function getTasksForUser(int $userId, ?string $userRole, ?int $userTeamId, array $productIds): array
    {
        if (empty($productIds) && $userRole !== 'Super Admin') {
            return [];
        }
        $builder = $this->select('tasks.*, products.name as product_name, products.product_type, products.max_allowed_hours, u.email as assignee_email, u.first_name as assignee_first, u.last_name as assignee_last')
            ->join('products', 'products.id = tasks.product_id')
            ->join('users u', 'u.id = tasks.assignee_id', 'left')
            ->groupStart()
                ->where('products.is_disabled', null)
                ->orWhere('products.is_disabled', 0)
            ->groupEnd();
        if ($userRole !== 'Super Admin') {
            $builder->whereIn('tasks.product_id', $productIds);
        }
        return $builder->orderBy('products.product_type', 'ASC')
            ->orderBy('products.name')
            ->orderBy('tasks.title')
            ->findAll();
    }

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

    /**
     * Tasks billable by user for time logging (FR-005e, Q13.5–Q13.6).
     * Leave: always billable. Non-leave: only if product.team_id = user.team_id.
     */
    public function getBillableByAssignee(int $userId, ?int $userTeamId): array
    {
        $builder = $this->select('tasks.*, products.name as product_name, products.product_type, products.max_allowed_hours')
            ->join('products', 'products.id = tasks.product_id')
            ->groupStart()
                ->where('products.is_disabled', null)
                ->orWhere('products.is_disabled', 0)
            ->groupEnd();

        $builder->groupStart();
        $builder->where('products.product_type', 'leave');
        if ($userTeamId !== null) {
            $builder->orGroupStart()
                ->where('products.team_id IS NOT NULL')
                ->where('products.team_id', $userTeamId)
                ->groupStart()
                    ->where('tasks.assignee_id', $userId)
                    ->orWhere('tasks.assignee_id IS NULL', null, false)
                ->groupEnd()
                ->groupEnd();
        }
        $builder->groupEnd();

        return $builder->orderBy('products.product_type', 'ASC')
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
