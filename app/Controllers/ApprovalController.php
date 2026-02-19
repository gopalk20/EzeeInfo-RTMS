<?php

namespace App\Controllers;

use App\Libraries\SmartyEngine;
use App\Models\ApprovalModel;
use App\Models\TaskModel;
use App\Models\TimeEntryModel;

class ApprovalController extends BaseController
{
    protected $helpers = ['form'];

    /**
     * Pending task completions and timesheet entries awaiting approval.
     */
    public function index()
    {
        $session = session();
        $userId = (int) $session->get('user_id');
        $userRole = $session->get('user_role');
        $taskModel = new TaskModel();
        $timeEntryModel = new TimeEntryModel();

        $db = \Config\Database::connect();
        $tasks = $db->table('tasks')
            ->select('tasks.*, products.name as product_name, users.email as assignee_email')
            ->join('products', 'products.id = tasks.product_id')
            ->join('users', 'users.id = tasks.assignee_id', 'left')
            ->where('tasks.status', 'Completed')
            ->where('tasks.locked', 0)
            ->orderBy('tasks.updated_at', 'DESC')
            ->get()
            ->getResultArray();

        $timeEntries = $timeEntryModel->getPendingForApprover($userId, $userRole);

        // Approved tasks (locked, approved by this user)
        $approvedTasksRaw = $db->table('approvals')
            ->select('tasks.id, tasks.title, tasks.status, products.name as product_name, users.email as assignee_email, approvals.approved_at')
            ->join('tasks', 'tasks.id = approvals.task_id')
            ->join('products', 'products.id = tasks.product_id')
            ->join('users', 'users.id = tasks.assignee_id', 'left')
            ->where('tasks.locked', 1)
            ->where('approvals.status', 'approved')
            ->where('approvals.approver_id', $userId)
            ->orderBy('approvals.approved_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();
        $seen = [];
        $approvedTasks = [];
        foreach ($approvedTasksRaw as $t) {
            if (!isset($seen[$t['id']])) {
                $seen[$t['id']] = true;
                $approvedTasks[] = $t;
            }
        }
        $approvedTasks = array_slice($approvedTasks, 0, 50);

        $approvedTimeEntries = $timeEntryModel->getApprovedForApprover($userId, $userRole);

        $smarty = new SmartyEngine();
        return $smarty->render('approval/pending.tpl', [
            'title'                => 'Pending Approvals',
            'tasks'                => $tasks,
            'time_entries'        => $timeEntries,
            'approved_tasks'       => $approvedTasks,
            'approved_time_entries'=> $approvedTimeEntries,
            'user_email'     => $session->get('user_email'),
            'user_role'      => $userRole,
            'is_super_admin'=> $userRole === 'Super Admin',
            'success'        => $session->getFlashdata('success'),
            'error'          => $session->getFlashdata('error'),
            'csrf'           => csrf_token(),
            'hash'           => csrf_hash(),
        ]);
    }

    public function approveTimesheet(int $entryId)
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to('/approval');
        }
        $userId = (int) session()->get('user_id');
        $userRole = session()->get('user_role');
        $timeEntryModel = new TimeEntryModel();
        $entry = $timeEntryModel->find($entryId);
        if (!$entry || ($entry['status'] ?? '') !== 'pending_approval') {
            return redirect()->to('/approval')->with('error', 'Time entry not found or already approved.');
        }
        $canApprove = $timeEntryModel->getPendingForApprover($userId, $userRole);
        $canApproveIds = array_column($canApprove, 'id');
        if (!in_array($entryId, $canApproveIds, true)) {
            return redirect()->to('/approval')->with('error', 'You cannot approve this time entry.');
        }
        $timeEntryModel->approveEntry($entryId, $userId);
        return redirect()->to('/approval')->with('success', 'Time entry approved.');
    }

    public function rejectTimesheet(int $entryId)
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to('/approval');
        }
        $userId = (int) session()->get('user_id');
        $userRole = session()->get('user_role');
        $timeEntryModel = new TimeEntryModel();
        $entry = $timeEntryModel->find($entryId);
        if (!$entry || ($entry['status'] ?? '') !== 'pending_approval') {
            return redirect()->to('/approval')->with('error', 'Time entry not found or already processed.');
        }
        $canApprove = $timeEntryModel->getPendingForApprover($userId, $userRole);
        $canApproveIds = array_column($canApprove, 'id');
        if (!in_array($entryId, $canApproveIds, true)) {
            return redirect()->to('/approval')->with('error', 'You cannot reject this time entry.');
        }
        $timeEntryModel->rejectEntry($entryId, $userId);
        return redirect()->to('/approval')->with('success', 'Time entry rejected.');
    }

    public function approve($taskId)
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to('/approval');
        }

        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);
        if (!$task || $task['locked']) {
            return redirect()->to('/approval')->with('error', 'Task not found or already approved.');
        }
        if ($task['status'] !== 'Completed') {
            return redirect()->to('/approval')->with('error', 'Only completed tasks can be approved.');
        }

        $approvalModel = new ApprovalModel();
        $approvalModel->insert([
            'task_id'     => $taskId,
            'approver_id' => $userId,
            'status'      => 'approved',
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $taskModel->update($taskId, ['locked' => 1]);

        return redirect()->to('/approval')->with('success', 'Task approved and locked.');
    }

    public function reject($taskId)
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to('/approval');
        }

        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);
        if (!$task || $task['locked']) {
            return redirect()->to('/approval')->with('error', 'Task not found or already approved.');
        }

        $feedback = $this->request->getPost('feedback') ?? '';

        $approvalModel = new ApprovalModel();
        $approvalModel->insert([
            'task_id'     => $taskId,
            'approver_id' => $userId,
            'status'      => 'rejected',
            'feedback'    => $feedback,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $taskModel->update($taskId, ['status' => 'Rework Requested']);

        return redirect()->to('/approval')->with('success', 'Task rejected and returned for rework.');
    }
}
