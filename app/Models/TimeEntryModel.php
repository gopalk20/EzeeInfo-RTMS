<?php

namespace App\Models;

use CodeIgniter\Model;

class TimeEntryModel extends Model
{
    protected $table            = 'time_entries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps   = true;
    protected $allowedFields   = ['task_id', 'user_id', 'work_date', 'hours', 'is_rework', 'status'];

    public function getByTask(int $taskId): array
    {
        return $this->where('task_id', $taskId)->orderBy('work_date', 'DESC')->findAll();
    }

    public function getByUser(int $userId, ?string $from = null, ?string $to = null): array
    {
        $builder = $this->select('time_entries.*, tasks.title as task_title, products.name as product_name')
            ->join('tasks', 'tasks.id = time_entries.task_id')
            ->join('products', 'products.id = tasks.product_id')
            ->where('time_entries.user_id', $userId);
        if ($from) {
            $builder->where('time_entries.work_date >=', $from);
        }
        if ($to) {
            $builder->where('time_entries.work_date <=', $to);
        }
        return $builder->orderBy('time_entries.work_date', 'DESC')->findAll();
    }

    public function getDailyTotal(int $userId, string $workDate): float
    {
        $result = $this->selectSum('hours')
            ->where('user_id', $userId)
            ->where('work_date', $workDate)
            ->first();
        return (float) ($result['hours'] ?? 0);
    }

    /**
     * Get time entries grouped by project for a user in a date range.
     */
    public function getGroupedByProject(int $userId, string $from, string $to): array
    {
        $db = \Config\Database::connect();
        return $db->table('time_entries')
            ->select('products.name as project_name, SUM(time_entries.hours) as total_hours')
            ->join('tasks', 'tasks.id = time_entries.task_id')
            ->join('products', 'products.id = tasks.product_id')
            ->where('time_entries.user_id', $userId)
            ->where('time_entries.work_date >=', $from)
            ->where('time_entries.work_date <=', $to)
            ->groupBy('products.id', 'products.name')
            ->orderBy('products.name')
            ->get()
            ->getResultArray();
    }

    /**
     * Get pending time entries for users who report to the given manager/lead.
     */
    public function getPendingForApprover(int $approverId, string $userRole): array
    {
        $db = $this->db;
        $builder = $db->table('time_entries')
            ->select('time_entries.*, tasks.title as task_title, products.name as product_name, users.email as user_email')
            ->join('tasks', 'tasks.id = time_entries.task_id')
            ->join('products', 'products.id = tasks.product_id')
            ->join('users', 'users.id = time_entries.user_id')
            ->where('time_entries.status', 'pending_approval');

        if ($userRole === 'Super Admin') {
            // Super Admin sees all pending entries
        } elseif ($userRole === 'Manager') {
            $builder->where('users.reporting_manager_id', $approverId);
        } else {
            $productIds = $db->table('products')->select('id')->where('product_lead_id', $approverId)->get()->getResultArray();
            $ids = array_column($productIds, 'id');
            if (empty($ids)) {
                return [];
            }
            $builder->whereIn('tasks.product_id', $ids);
        }

        return $builder->orderBy('time_entries.work_date', 'DESC')->get()->getResultArray();
    }

    /**
     * Approve time entry.
     */
    public function approveEntry(int $entryId, int $approverId): bool
    {
        return $this->update($entryId, ['status' => 'approved']);
    }

    /**
     * Get consolidated time entries for a manager's/lead's reporting persons.
     */
    public function getConsolidatedForApprover(int $approverId, string $userRole, string $from, string $to): array
    {
        $db = $this->db;
        if ($userRole === 'Super Admin') {
            return $db->table('time_entries')
                ->select('time_entries.*, tasks.title as task_title, products.name as product_name, users.email as user_email')
                ->join('tasks', 'tasks.id = time_entries.task_id')
                ->join('products', 'products.id = tasks.product_id')
                ->join('users', 'users.id = time_entries.user_id')
                ->where('time_entries.work_date >=', $from)
                ->where('time_entries.work_date <=', $to)
                ->orderBy('users.email')->orderBy('time_entries.work_date', 'DESC')
                ->get()->getResultArray();
        }
        if ($userRole === 'Manager') {
            return $db->table('time_entries')
                ->select('time_entries.*, tasks.title as task_title, products.name as product_name, users.email as user_email')
                ->join('tasks', 'tasks.id = time_entries.task_id')
                ->join('products', 'products.id = tasks.product_id')
                ->join('users', 'users.id = time_entries.user_id')
                ->where('users.reporting_manager_id', $approverId)
                ->where('time_entries.work_date >=', $from)
                ->where('time_entries.work_date <=', $to)
                ->orderBy('users.email')->orderBy('time_entries.work_date', 'DESC')
                ->get()->getResultArray();
        }
        $productIds = $db->table('products')->select('id')->where('product_lead_id', $approverId)->get()->getResultArray();
        $ids = array_column($productIds, 'id');
        if (empty($ids)) {
            return [];
        }
        return $db->table('time_entries')
            ->select('time_entries.*, tasks.title as task_title, products.name as product_name, users.email as user_email')
            ->join('tasks', 'tasks.id = time_entries.task_id')
            ->join('products', 'products.id = tasks.product_id')
            ->join('users', 'users.id = time_entries.user_id')
            ->whereIn('tasks.product_id', $ids)
            ->where('time_entries.work_date >=', $from)
            ->where('time_entries.work_date <=', $to)
            ->orderBy('users.email')->orderBy('time_entries.work_date', 'DESC')
            ->get()->getResultArray();
    }
}
