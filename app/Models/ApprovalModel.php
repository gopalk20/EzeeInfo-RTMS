<?php

namespace App\Models;

use CodeIgniter\Model;

class ApprovalModel extends Model
{
    protected $table            = 'approvals';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps   = true;
    protected $allowedFields   = ['task_id', 'approver_id', 'status', 'feedback', 'approved_at'];

    public function getByTask(int $taskId): array
    {
        return $this->select('approvals.*, users.email as approver_email')
            ->join('users', 'users.id = approvals.approver_id')
            ->where('approvals.task_id', $taskId)
            ->orderBy('approvals.approved_at', 'DESC')
            ->findAll();
    }

    public function isApproved(int $taskId): bool
    {
        $row = $this->where('task_id', $taskId)->where('status', 'approved')->first();
        return $row !== null;
    }
}
