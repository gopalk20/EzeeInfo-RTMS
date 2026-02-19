<?php

namespace App\Controllers;

use App\Libraries\ConfigService;
use App\Libraries\SmartyEngine;
use App\Models\ProductModel;
use App\Models\ResourceCostModel;
use App\Models\TaskModel;
use App\Models\TimeEntryModel;
use App\Models\UserModel;

class TimesheetController extends BaseController
{
    protected $helpers = ['form'];

    /**
     * List my time entries and show form to log new time.
     */
    public function index()
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        $timeEntryModel = new TimeEntryModel();
        $from = date('Y-m-01');
        $to = date('Y-m-t');
        $entries = $timeEntryModel->getByUser($userId, $from, $to);

        $taskModel = new TaskModel();
        $tasks = $taskModel->getByAssignee($userId);

        $configService = new ConfigService();

        $smarty = new SmartyEngine();
        return $smarty->render('timesheet/index.tpl', [
            'title'              => 'My Timesheet',
            'nav_active'          => 'timesheet',
            'entries'            => $entries,
            'tasks'              => $tasks,
            'default_work_date'   => date('Y-m-d'),
            'user_email'         => $session->get('user_email'),
            'user_role'          => $session->get('user_role'),
            'is_super_admin'    => $session->get('user_role') === 'Super Admin',
            'daily_hours_limit'  => $configService->getDailyHoursLimit(),
            'csrf'               => csrf_token(),
            'hash'               => csrf_hash(),
            'success'            => $session->getFlashdata('success'),
            'error'              => $session->getFlashdata('error'),
        ]);
    }

    /**
     * Weekly time sheet grid: tasks as rows, days as columns.
     */
    public function sheetView()
    {
        $session = session();
        $userId = (int) $session->get('user_id');
        $timeEntryModel = new TimeEntryModel();
        $taskModel = new TaskModel();
        $productModel = new ProductModel();

        $dateParam = $this->request->getGet('date');
        $today = date('Y-m-d');
        $baseDate = ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) ? $dateParam : $today;
        $ts = strtotime($baseDate);
        $dow = (int) date('w', $ts);
        $monday = date('Y-m-d', strtotime($baseDate . ' -' . ($dow ? $dow - 1 : 6) . ' days'));
        $from = $monday;
        $to = date('Y-m-d', strtotime($from . ' +6 days'));

        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $d = date('Y-m-d', strtotime($from . " +{$i} days"));
            $dn = (int) date('w', strtotime($d));
            $weekDays[] = [
                'date' => $d,
                'day_short' => $dayNames[$dn],
                'label' => date('M j', strtotime($d)) . ' ' . $dayNames[$dn],
            ];
        }

        $tasks = $taskModel->select('tasks.*, products.name as product_name, products.max_allowed_hours')
            ->join('products', 'products.id = tasks.product_id')
            ->where('tasks.assignee_id', $userId)
            ->groupStart()->where('products.is_disabled', null)->orWhere('products.is_disabled', 0)->groupEnd()
            ->orderBy('products.name')->orderBy('tasks.title')
            ->findAll();

        $entries = $timeEntryModel->getByUser($userId, $from, $to);

        $hoursByTaskDate = [];
        foreach ($entries as $e) {
            $tid = (int) $e['task_id'];
            $wd = $e['work_date'];
            if (!isset($hoursByTaskDate[$tid])) {
                $hoursByTaskDate[$tid] = [];
            }
            $hoursByTaskDate[$tid][$wd] = ((float) ($hoursByTaskDate[$tid][$wd] ?? 0)) + (float) $e['hours'];
        }

        $taskToProduct = [];
        foreach ($tasks as $t) {
            $taskToProduct[(int) $t['id']] = (int) $t['product_id'];
        }
        $productIds = array_unique(array_values($taskToProduct));
        $productHoursUsed = [];
        if (!empty($productIds)) {
            $db = \Config\Database::connect();
            $res = $db->table('time_entries')
                ->select('tasks.product_id, SUM(time_entries.hours) as total')
                ->join('tasks', 'tasks.id = time_entries.task_id')
                ->whereIn('tasks.product_id', $productIds)
                ->groupBy('tasks.product_id')
                ->get()->getResultArray();
            foreach ($res as $r) {
                $productHoursUsed[(int) $r['product_id']] = (float) $r['total'];
            }
        }

        $rows = [];
        $dailyTotals = array_fill(0, 7, 0.0);
        foreach ($tasks as $t) {
            $tid = (int) $t['id'];
            $pid = (int) $t['product_id'];
            $maxHours = $t['max_allowed_hours'] ? (float) $t['max_allowed_hours'] : null;
            $usedHours = $productHoursUsed[$pid] ?? 0;
            $dayHours = [];
            $rowTotal = 0.0;
            for ($i = 0; $i < 7; $i++) {
                $d = $weekDays[$i]['date'];
                $h = $hoursByTaskDate[$tid][$d] ?? 0;
                $dayHours[] = $h;
                $rowTotal += $h;
                $dailyTotals[$i] += $h;
            }
            $pctUsed = $maxHours > 0 ? min(100, ($usedHours / $maxHours) * 100) : 0;
            $rows[] = [
                'task_id' => $tid,
                'task_title' => $t['title'],
                'product_name' => $t['product_name'],
                'product_id' => $pid,
                'max_hours' => $maxHours,
                'used_hours' => $usedHours,
                'pct_used' => $pctUsed,
                'day_hours' => $dayHours,
                'row_total' => $rowTotal,
            ];
        }
        $weekTotal = array_sum($dailyTotals);

        $smarty = new SmartyEngine();
        return $smarty->render('timesheet/sheet.tpl', [
            'title'        => 'Time Sheet',
            'nav_active'   => 'sheet',
            'from'         => $from,
            'to'           => $to,
            'week_days'    => $weekDays,
            'rows'         => $rows,
            'daily_totals' => $dailyTotals,
            'week_total'   => $weekTotal,
            'tasks'        => $tasks,
            'success'      => $session->getFlashdata('success'),
            'error'        => $session->getFlashdata('error'),
            'user_email'   => $session->get('user_email'),
            'user_role'    => $session->get('user_role'),
            'is_super_admin' => $session->get('user_role') === 'Super Admin',
            'csrf'         => csrf_token(),
            'hash'         => csrf_hash(),
        ]);
    }

    /**
     * View timesheet summary by period (daily/weekly/monthly) - Project Name | Time | Total.
     * Supports date/week/month params to navigate: ?period=daily&date=Y-m-d, ?period=weekly&date=Y-m-d (any day in week), ?period=monthly&month=Y-m
     */
    public function viewSummary()
    {
        $session = session();
        $userId = (int) $session->get('user_id');
        $period = $this->request->getGet('period') ?: 'monthly';
        $timeEntryModel = new TimeEntryModel();
        $today = date('Y-m-d');

        if ($period === 'daily') {
            $dateParam = $this->request->getGet('date');
            $baseDate = ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) ? $dateParam : $today;
            $from = $to = $baseDate;
        } elseif ($period === 'weekly') {
            $dateParam = $this->request->getGet('date');
            $baseDate = ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) ? $dateParam : $today;
            $ts = strtotime($baseDate);
            $dow = (int) date('w', $ts);
            $monday = date('Y-m-d', strtotime($baseDate . ' -' . ($dow ? $dow - 1 : 6) . ' days'));
            $from = $monday;
            $to = date('Y-m-d', strtotime($from . ' +6 days'));
        } else {
            $monthParam = $this->request->getGet('month');
            if ($monthParam && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
                $from = $monthParam . '-01';
                $to = date('Y-m-t', strtotime($from));
            } else {
                $from = date('Y-m-01');
                $to = date('Y-m-t');
            }
        }

        $grouped = $timeEntryModel->getGroupedByProject($userId, $from, $to);
        $entries = $timeEntryModel->getByUser($userId, $from, $to);
        $grandTotal = array_sum(array_column($grouped, 'total_hours'));

        $monthValue = substr($from, 0, 7);

        $smarty = new SmartyEngine();
        return $smarty->render('timesheet/view.tpl', [
            'title'       => 'Timesheet Summary',
            'period'      => $period,
            'from'        => $from,
            'to'          => $to,
            'month_value' => $monthValue,
            'success'     => $session->getFlashdata('success'),
            'error'       => $session->getFlashdata('error'),
            'grouped'     => $grouped,
            'entries'     => $entries,
            'grand_total' => $grandTotal,
            'user_email'  => $session->get('user_email'),
            'user_role'   => $session->get('user_role'),
            'is_super_admin' => $session->get('user_role') === 'Super Admin',
        ]);
    }

    /**
     * Team timesheet in resource-allocation format: one row per employee with allocation, billing, hours.
     */
    public function teamView()
    {
        $session = session();
        $userId = (int) $session->get('user_id');
        $userRole = $session->get('user_role');
        $period = $this->request->getGet('period') ?: 'monthly';
        $timeEntryModel = new TimeEntryModel();
        $userModel = new UserModel();
        $costModel = new ResourceCostModel();
        $configService = new ConfigService();

        $today = date('Y-m-d');
        if ($period === 'daily') {
            $dateParam = $this->request->getGet('date');
            $baseDate = ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) ? $dateParam : $today;
            $from = $to = $baseDate;
        } elseif ($period === 'weekly') {
            $dateParam = $this->request->getGet('date');
            $baseDate = ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) ? $dateParam : $today;
            $ts = strtotime($baseDate);
            $dow = (int) date('w', $ts);
            $monday = date('Y-m-d', strtotime($baseDate . ' -' . ($dow ? $dow - 1 : 6) . ' days'));
            $from = $monday;
            $to = date('Y-m-d', strtotime($from . ' +6 days'));
        } else {
            $monthParam = $this->request->getGet('month');
            if ($monthParam && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
                $from = $monthParam . '-01';
                $to = date('Y-m-t', strtotime($from));
            } else {
                $from = date('Y-m-01');
                $to = date('Y-m-t');
            }
        }

        $entries = $timeEntryModel->getConsolidatedForApprover($userId, $userRole, $from, $to);

        $hoursByUser = [];
        foreach ($entries as $e) {
            $uid = (int) $e['user_id'];
            $hoursByUser[$uid] = ((float) ($hoursByUser[$uid] ?? 0)) + (float) $e['hours'];
        }

        $teamMemberIds = $this->getTeamMemberIds($userId, $userRole);
        $allUserIds = array_unique(array_merge(array_keys($hoursByUser), $teamMemberIds));

        $standardHours = $configService->getStandardHours();
        $daysInPeriod = (int) ((strtotime($to) - strtotime($from)) / 86400) + 1;
        $workDaysEstimate = (int) ceil($daysInPeriod / 7 * 5);
        $allocatedHoursDefault = round($workDaysEstimate * $standardHours, 1);

        $rows = [];
        foreach ($allUserIds as $uid) {
            $user = $userModel->find($uid);
            if (!$user || empty($user['is_active'])) {
                continue;
            }
            $displayName = $userModel->getDisplayName($user);
            $role = $userModel->getRole($user);
            $roleName = $role['name'] ?? 'Employee';
            $team = $userModel->getTeam($user);
            $teamName = $team['name'] ?? '—';
            $hoursSpent = $hoursByUser[$uid] ?? 0;
            $costRow = $costModel->getForUser($uid);
            $monthlyCost = $costRow ? (float) $costRow['monthly_cost'] : 0;
            $billingRate = $configService->calculateHourlyCost($monthlyCost);
            $hoursAllocated = $allocatedHoursDefault;
            $pctUsed = $hoursAllocated > 0 ? min(100, ($hoursSpent / $hoursAllocated) * 100) : 0;

            $rows[] = [
                'user_id'        => $uid,
                'display_name'   => $displayName ?: $user['email'],
                'email'          => $user['email'],
                'role_name'      => $roleName,
                'team_name'      => $teamName,
                'allocation'     => $from . ' – ' . $to,
                'allocation_pct' => '100%',
                'billing_role'   => $roleName,
                'billing_rate'   => $billingRate,
                'hours_spent'    => $hoursSpent,
                'hours_allocated'=> $hoursAllocated,
                'pct_used'       => $pctUsed,
            ];
        }

        usort($rows, fn($a, $b) => strcasecmp($a['display_name'], $b['display_name']));

        $monthValue = substr($from, 0, 7);

        $smarty = new SmartyEngine();
        return $smarty->render('timesheet/team.tpl', [
            'title'           => 'Team Timesheet',
            'nav_active'      => 'team',
            'period'          => $period,
            'from'            => $from,
            'to'              => $to,
            'month_value'     => $monthValue,
            'rows'            => $rows,
            'entries'         => $entries,
            'user_email'      => $session->get('user_email'),
            'user_role'       => $userRole,
            'is_super_admin'  => $userRole === 'Super Admin',
        ]);
    }

    protected function getTeamMemberIds(int $managerId, string $userRole): array
    {
        $userModel = new UserModel();
        $db = \Config\Database::connect();
        if ($userRole === 'Super Admin') {
            $r = $db->table('users')->select('id')->where('is_active', 1)->get()->getResultArray();
            return array_column($r, 'id');
        }
        if ($userRole === 'Manager') {
            $r = $db->table('users')->select('id')->where('reporting_manager_id', $managerId)->get()->getResultArray();
            return array_column($r, 'id');
        }
        $productIds = $db->table('products')->select('id')->where('product_lead_id', $managerId)->get()->getResultArray();
        $pids = array_column($productIds, 'id');
        if (empty($pids)) {
            return [];
        }
        $memberIds = $db->table('product_members')->select('user_id')->whereIn('product_id', $pids)->get()->getResultArray();
        $ids = array_unique(array_column($memberIds, 'user_id'));
        $assigneeIds = $db->table('tasks')->select('assignee_id')->whereIn('product_id', $pids)->where('assignee_id IS NOT NULL')->get()->getResultArray();
        foreach ($assigneeIds as $a) {
            $ids[] = $a['assignee_id'];
        }
        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * View a specific team member's time entries for the period.
     */
    public function teamDetails()
    {
        $session = session();
        $managerId = (int) $session->get('user_id');
        $userRole = $session->get('user_role');
        $targetUserId = (int) $this->request->getGet('user_id');
        $from = $this->request->getGet('from') ?: date('Y-m-01');
        $to = $this->request->getGet('to') ?: date('Y-m-t');
        $period = $this->request->getGet('period') ?: 'monthly';

        $memberIds = $this->getTeamMemberIds($managerId, $userRole);
        if (!in_array($targetUserId, $memberIds, true)) {
            return redirect()->to('/timesheet/team')->with('error', 'Access denied.');
        }

        $timeEntryModel = new TimeEntryModel();
        $userModel = new UserModel();
        $entries = $timeEntryModel->getByUser($targetUserId, $from, $to);
        $user = $userModel->find($targetUserId);
        $displayName = $user ? $userModel->getDisplayName($user) : 'Unknown';

        $monthValue = substr($from, 0, 7);
        $smarty = new SmartyEngine();
        return $smarty->render('timesheet/team_details.tpl', [
            'title'       => 'Team Member Details',
            'nav_active'  => 'team',
            'entries'     => $entries,
            'display_name'=> $displayName,
            'from'        => $from,
            'to'          => $to,
            'period'      => $period,
            'month_value' => $monthValue,
            'user_email'  => $session->get('user_email'),
        ]);
    }

    /**
     * Edit a time entry (only if pending approval).
     */
    public function edit(int $id)
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        $timeEntryModel = new TimeEntryModel();
        $entry = $timeEntryModel->find($id);
        if (!$entry || (int) $entry['user_id'] !== $userId) {
            return redirect()->to('/timesheet')->with('error', 'Time entry not found.');
        }
        if (($entry['status'] ?? '') !== 'pending_approval') {
            return redirect()->to('/timesheet')->with('error', 'Cannot edit approved time entry.');
        }

        $taskModel = new TaskModel();
        $task = $taskModel->select('tasks.*, products.name as product_name')
            ->join('products', 'products.id = tasks.product_id')
            ->find($entry['task_id']);
        $task = $task ?: $taskModel->find($entry['task_id']);

        $smarty = new SmartyEngine();
        return $smarty->render('timesheet/edit.tpl', [
            'title'     => 'Edit Time Entry',
            'entry'     => $entry,
            'task'      => $task,
            'user_email'=> $session->get('user_email'),
            'user_role' => $session->get('user_role'),
            'is_super_admin' => $session->get('user_role') === 'Super Admin',
            'csrf'      => csrf_token(),
            'hash'      => csrf_hash(),
        ]);
    }

    /**
     * Update a time entry (only if pending approval).
     */
    public function update(int $id)
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/timesheet');
        }

        $timeEntryModel = new TimeEntryModel();
        $entry = $timeEntryModel->find($id);
        if (!$entry || (int) $entry['user_id'] !== $userId) {
            return redirect()->to('/timesheet')->with('error', 'Time entry not found.');
        }
        if (($entry['status'] ?? '') !== 'pending_approval') {
            return redirect()->to('/timesheet')->with('error', 'Cannot edit approved time entry.');
        }

        $rules = [
            'hours' => 'required|decimal|greater_than[0]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->to("/timesheet/edit/{$id}")->with('error', implode(' ', $this->validator->getErrors()));
        }

        $hours = (float) $this->request->getPost('hours');
        $isRework = (bool) $this->request->getPost('is_rework');
        $configService = new ConfigService();
        $dailyTotal = $timeEntryModel->getDailyTotal($userId, $entry['work_date']) - (float) $entry['hours'];
        if ($dailyTotal + $hours > $configService->getDailyHoursLimit()) {
            return redirect()->to("/timesheet/edit/{$id}")->with('error', "Daily total would exceed limit.");
        }

        $timeEntryModel->update($id, [
            'hours'     => $hours,
            'is_rework' => $isRework ? 1 : 0,
        ]);

        return redirect()->to('/timesheet')->with('success', 'Time entry updated successfully.');
    }

    /**
     * Log new time entry.
     */
    public function log()
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/timesheet');
        }

        $rules = [
            'task_id'   => 'required|integer',
            'work_date' => 'required|valid_date',
            'hours'     => 'required|decimal|greater_than[0]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->to('/timesheet')->with('error', implode(' ', $this->validator->getErrors()));
        }

        $taskId = (int) $this->request->getPost('task_id');
        $workDate = $this->request->getPost('work_date');
        $hours = (float) $this->request->getPost('hours');
        $isRework = (bool) $this->request->getPost('is_rework');

        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);
        if (!$task || (int) $task['assignee_id'] !== $userId) {
            return redirect()->to('/timesheet')->with('error', 'Invalid task or not assigned to you.');
        }
        if (!empty($task['locked'])) {
            return redirect()->to('/timesheet')->with('error', 'Cannot log time for locked/completed task.');
        }

        $configService = new ConfigService();
        $dailyTotal = (new TimeEntryModel())->getDailyTotal($userId, $workDate);
        $limit = $configService->getDailyHoursLimit();
        if ($dailyTotal + $hours > $limit) {
            return redirect()->to('/timesheet')->with('error', "Daily total would exceed {$limit} hours (current: {$dailyTotal}).");
        }

        $timeEntryModel = new TimeEntryModel();
        $timeEntryModel->insert([
            'task_id'   => $taskId,
            'user_id'   => $userId,
            'work_date' => $workDate,
            'hours'     => $hours,
            'is_rework' => $isRework ? 1 : 0,
            'status'    => 'pending_approval',
        ]);

        $session->setFlashdata('success', 'Timesheet submitted successfully! Your time entry has been recorded.');
        return redirect()->to('/timesheet/view?period=daily&date=' . urlencode($workDate));
    }
}
