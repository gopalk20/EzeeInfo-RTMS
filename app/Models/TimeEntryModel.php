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
    protected $allowedFields   = ['task_id', 'user_id', 'work_date', 'hours', 'description', 'is_rework', 'status'];

    public function getByTask(int $taskId): array
    {
        return $this->where('task_id', $taskId)->orderBy('work_date', 'DESC')->findAll();
    }

    public function getByUser(int $userId, ?string $from = null, ?string $to = null): array
    {
        $builder = $this->select('time_entries.*, tasks.title as task_title, products.name as product_name')
            ->join('tasks', 'tasks.id = time_entries.task_id', 'left')
            ->join('products', 'products.id = tasks.product_id', 'left')
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
     * Get approved time entries for users who report to the given manager/lead.
     */
    public function getApprovedForApprover(int $approverId, string $userRole, int $limit = 50): array
    {
        $db = $this->db;
        $builder = $db->table('time_entries')
            ->select('time_entries.*, tasks.title as task_title, products.name as product_name, users.email as user_email')
            ->join('tasks', 'tasks.id = time_entries.task_id')
            ->join('products', 'products.id = tasks.product_id')
            ->join('users', 'users.id = time_entries.user_id')
            ->where('time_entries.status', 'approved');

        if ($userRole === 'Super Admin') {
            // Super Admin sees all approved entries
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

        return $builder->orderBy('time_entries.updated_at', 'DESC')->limit($limit)->get()->getResultArray();
    }

    /**
     * Approve time entry.
     */
    public function approveEntry(int $entryId, int $approverId): bool
    {
        return $this->update($entryId, ['status' => 'approved']);
    }

    /**
     * Reject time entry (returns to draft so user can resubmit).
     */
    public function rejectEntry(int $entryId, int $rejecterId): bool
    {
        return $this->update($entryId, ['status' => 'rejected']);
    }

    /**
     * Get billable and non-billable hours for a date range (all users, Super Admin view).
     * Billable = products where product_type IS NULL or != 'leave'. Non-billable = product_type = 'leave'.
     */
    public function getBillableNonBillableHours(string $from, string $to): array
    {
        $rows = $this->db->query("
            SELECT
                COALESCE(products.product_type, 'billable') as type,
                SUM(te.hours) as total
            FROM time_entries te
            JOIN tasks ON tasks.id = te.task_id
            JOIN products ON products.id = tasks.product_id
            WHERE te.work_date >= ? AND te.work_date <= ?
            GROUP BY type
        ", [$from, $to])->getResultArray();
        $billable = 0.0;
        $nonBillable = 0.0;
        foreach ($rows as $r) {
            if (($r['type'] ?? '') === 'leave') {
                $nonBillable += (float) ($r['total'] ?? 0);
            } else {
                $billable += (float) ($r['total'] ?? 0);
            }
        }
        return ['billable' => $billable, 'non_billable' => $nonBillable];
    }

    /**
     * Get hours by month for work hours summary (billable vs non-billable).
     * Returns last N months in chronological order, with zeros for empty months.
     */
    public function getMonthlyHoursSummary(int $months = 7): array
    {
        $end = date('Y-m-t');
        $start = date('Y-m-01', strtotime("-{$months} months"));
        $rows = $this->db->query("
            SELECT
                DATE_FORMAT(te.work_date, '%Y-%m') as month,
                COALESCE(products.product_type, 'billable') as type,
                SUM(te.hours) as total
            FROM time_entries te
            JOIN tasks ON tasks.id = te.task_id
            JOIN products ON products.id = tasks.product_id
            WHERE te.work_date >= ? AND te.work_date <= ?
            GROUP BY month, type
        ", [$start, $end])->getResultArray();
        $byMonth = [];
        foreach ($rows as $r) {
            $m = $r['month'];
            if (!isset($byMonth[$m])) {
                $byMonth[$m] = ['billable' => 0.0, 'non_billable' => 0.0];
            }
            if (($r['type'] ?? '') === 'leave') {
                $byMonth[$m]['non_billable'] += (float) ($r['total'] ?? 0);
            } else {
                $byMonth[$m]['billable'] += (float) ($r['total'] ?? 0);
            }
        }
        $sorted = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $m = date('Y-m', strtotime("-{$i} months"));
            $sorted[$m] = $byMonth[$m] ?? ['billable' => 0.0, 'non_billable' => 0.0];
        }
        return $sorted;
    }

    /**
     * Get hours by product for resource allocation (includes leave types).
     */
    public function getHoursByProduct(string $from, string $to): array
    {
        return $this->db->query("
            SELECT
                products.id,
                products.name,
                products.product_type,
                SUM(te.hours) as total
            FROM time_entries te
            JOIN tasks ON tasks.id = te.task_id
            JOIN products ON products.id = tasks.product_id
            WHERE te.work_date >= ? AND te.work_date <= ?
            GROUP BY products.id, products.name, products.product_type
            HAVING total > 0
            ORDER BY total DESC
        ", [$from, $to])->getResultArray();
    }

    /**
     * Get pending approvers with their timesheet counts (Managers + Product Leads who have pending entries).
     */
    public function getPendingApproversList(): array
    {
        $db = $this->db;
        $roleIds = $db->table('roles')->whereIn('name', ['Manager', 'Product Lead', 'Super Admin'])->get()->getResultArray();
        $rids = array_column($roleIds, 'id');
        if (empty($rids)) {
            return [];
        }
        $approvers = $db->table('users')
            ->select('users.id, users.email, users.first_name, users.last_name, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->whereIn('users.role_id', $rids)
            ->where('users.is_active', 1)
            ->get()
            ->getResultArray();
        $result = [];
        foreach ($approvers as $a) {
            $entries = $this->getPendingForApprover((int) $a['id'], $a['role_name']);
            $count = count($entries);
            if ($count > 0) {
                $result[] = [
                    'id'              => $a['id'],
                    'email'           => $a['email'],
                    'first_name'      => $a['first_name'] ?? '',
                    'last_name'       => $a['last_name'] ?? '',
                    'role_name'       => $a['role_name'],
                    'timesheet_count' => $count,
                ];
            }
        }
        usort($result, fn($a, $b) => $b['timesheet_count'] <=> $a['timesheet_count']);
        return array_slice($result, 0, 10);
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
