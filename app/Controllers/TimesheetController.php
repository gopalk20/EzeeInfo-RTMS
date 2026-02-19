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
    protected $helpers = ['form', 'url'];

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

        $dateParam = $this->request->getGet('date');
        $defaultWorkDate = ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam))
            ? $dateParam
            : date('Y-m-d');

        $success = $session->getFlashdata('success');
        $error   = $session->getFlashdata('error');
        if (!$success && $this->request->getGet('saved') === '1') {
            $success = 'Timesheet submitted successfully! Your time entry has been recorded.';
        }
        if (!$error && $this->request->getGet('err')) {
            $error = $this->request->getGet('err');
        }

        $smarty = new SmartyEngine();
        return $smarty->render('timesheet/index.tpl', [
            'title'              => 'My Timesheet',
            'nav_active'          => 'timesheet',
            'entries'            => $entries,
            'tasks'              => $tasks,
            'default_work_date'   => $defaultWorkDate,
            'user_email'         => $session->get('user_email'),
            'user_role'          => $session->get('user_role'),
            'is_super_admin'    => $session->get('user_role') === 'Super Admin',
            'daily_hours_limit'  => $configService->getDailyHoursLimit(),
            'csrf'               => csrf_token(),
            'hash'               => csrf_hash(),
            'success'            => $success,
            'error'              => $error,
            'base_url'           => base_url(),
            'log_action_url'     => site_url('timesheet/log'),
        ]);
    }

    /**
     * Time sheet grid: tasks as rows, days/weeks as columns. Supports daily, weekly, monthly.
     */
    public function sheetView()
    {
        $session = session();
        $userId = (int) $session->get('user_id');
        $period = $this->request->getGet('period') ?: 'weekly';
        $timeEntryModel = new TimeEntryModel();
        $taskModel = new TaskModel();

        $today = date('Y-m-d');
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        if ($period === 'daily') {
            $dateParam = $this->request->getGet('date');
            $baseDate = ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) ? $dateParam : $today;
            $from = $to = $baseDate;
            $weekDays = [[
                'date' => $baseDate,
                'day_short' => $dayNames[(int) date('w', strtotime($baseDate))],
                'label' => date('M j, Y', strtotime($baseDate)),
            ]];
        } elseif ($period === 'monthly') {
            $monthParam = $this->request->getGet('month');
            if ($monthParam && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
                $from = $monthParam . '-01';
                $to = date('Y-m-t', strtotime($from));
            } else {
                $from = date('Y-m-01');
                $to = date('Y-m-t');
            }
            $weeksInMonth = [];
            $current = strtotime($from);
            $end = strtotime($to);
            $w = 1;
            while ($current <= $end) {
                $weekStart = date('Y-m-d', $current);
                $weekEnd = date('Y-m-d', min(strtotime($weekStart . ' +6 days'), $end));
                $weeksInMonth[] = [
                    'week_num' => $w++,
                    'from' => $weekStart,
                    'to' => $weekEnd,
                    'label' => date('M j', strtotime($weekStart)) . '–' . date('j', strtotime($weekEnd)),
                ];
                $current = strtotime($weekStart . ' +7 days');
            }
            $weekDays = $weeksInMonth;
        } else {
            $dateParam = $this->request->getGet('date');
            $baseDate = ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) ? $dateParam : $today;
            $ts = strtotime($baseDate);
            $dow = (int) date('w', $ts);
            $monday = date('Y-m-d', strtotime($baseDate . ' -' . ($dow ? $dow - 1 : 6) . ' days'));
            $from = $monday;
            $to = date('Y-m-d', strtotime($from . ' +6 days'));
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
        }

        $tasks = $taskModel->getByAssignee($userId);

        $entries = $timeEntryModel->getByUser($userId, $from, $to);

        $hoursByTaskDate = [];
        foreach ($entries as $e) {
            $tid = (int) $e['task_id'];
            $wd = substr((string) ($e['work_date'] ?? ''), 0, 10);
            if ($wd === '' || strlen($wd) < 10) {
                continue;
            }
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

        $hoursByTaskWeek = [];
        if ($period === 'monthly') {
            foreach ($entries as $e) {
                $tid = (int) $e['task_id'];
                $wd = substr((string) ($e['work_date'] ?? ''), 0, 10);
                if ($wd === '' || strlen($wd) < 10) {
                    continue;
                }
                foreach ($weekDays as $i => $col) {
                    $weekFrom = $col['from'] ?? '';
                    $weekTo = $col['to'] ?? '';
                    if ($weekFrom && $weekTo && $wd >= $weekFrom && $wd <= $weekTo) {
                        if (!isset($hoursByTaskWeek[$tid][$i])) {
                            $hoursByTaskWeek[$tid][$i] = 0.0;
                        }
                        $hoursByTaskWeek[$tid][$i] += (float) $e['hours'];
                        break;
                    }
                }
            }
        }

        $rows = [];
        $numCols = count($weekDays);
        $dailyTotals = array_fill(0, $numCols, 0.0);

        foreach ($tasks as $t) {
            $tid = (int) $t['id'];
            $pid = (int) $t['product_id'];
            $maxHours = !empty($t['max_allowed_hours']) ? (float) $t['max_allowed_hours'] : null;
            $usedHours = $productHoursUsed[$pid] ?? 0;
            $dayHours = [];
            $rowTotal = 0.0;
            for ($i = 0; $i < $numCols; $i++) {
                $col = $weekDays[$i];
                $d = $col['date'] ?? null;
                if ($d) {
                    $h = $hoursByTaskDate[$tid][$d] ?? 0;
                } else {
                    $h = $hoursByTaskWeek[$tid][$i] ?? 0;
                }
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
        $periodTotal = array_sum($dailyTotals);
        $monthValue = substr($from, 0, 7);

        $smarty = new SmartyEngine();
        return $smarty->render('timesheet/sheet.tpl', [
            'title'        => 'Time Sheet',
            'nav_active'   => 'sheet',
            'period'       => $period,
            'from'         => $from,
            'to'           => $to,
            'month_value'  => $monthValue,
            'form_action'  => site_url('timesheet/sheet'),
            'grid_colspan'  => count($weekDays) + 2,
            'week_days'    => $weekDays,
            'rows'         => $rows,
            'daily_totals' => $dailyTotals,
            'period_total' => $periodTotal,
            'tasks'        => $tasks,
            'entries'      => $entries,
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
        $period = $this->request->getGet('period') ?: 'daily';
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
            'nav_active'  => 'view',
            'period'      => $period,
            'from'        => $from,
            'to'          => $to,
            'month_value' => $monthValue,
            'form_action'  => site_url('timesheet/view'),
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

        $teamFilter = $this->request->getGet('team');
        $teamModel = new \App\Models\TeamModel();
        $teams = $teamModel->orderBy('name')->findAll();

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
            if ($teamFilter !== null && $teamFilter !== '') {
                $userTeam = $userModel->getTeam($user);
                $userTeamName = $userTeam['name'] ?? '';
                if ($userTeamName !== $teamFilter) {
                    continue;
                }
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
            'filter_team'     => $teamFilter ?? '',
            'teams'           => $teams,
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
        $teamFilter = $this->request->getGet('team');

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
            'filter_team' => $teamFilter ?? '',
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
            'title'       => 'Edit Time Entry',
            'entry'       => $entry,
            'task'        => $task,
            'back_to_sheet_date' => $entry['work_date'] ?? '',
            'user_email'  => $session->get('user_email'),
            'user_role'   => $session->get('user_role'),
            'is_super_admin' => $session->get('user_role') === 'Super Admin',
            'csrf'        => csrf_token(),
            'hash'        => csrf_hash(),
        ]);
    }

    /**
     * Update a time entry (only if pending approval).
     */
    public function update(int $id)
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        if (strtoupper($this->request->getMethod()) !== 'POST') {
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
            'hours' => 'required|decimal|greater_than[0]|less_than_equal_to[24]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->to("/timesheet/edit/{$id}")->with('error', implode(' ', $this->validator->getErrors()));
        }

        $hours = (float) $this->request->getPost('hours');
        $description = trim((string) $this->request->getPost('description'));
        $isRework = (bool) $this->request->getPost('is_rework');
        $configService = new ConfigService();
        $limit = min(24.0, $configService->getDailyHoursLimit());
        $dailyTotal = $timeEntryModel->getDailyTotal($userId, $entry['work_date']) - (float) $entry['hours'];
        if ($dailyTotal + $hours > $limit) {
            return redirect()->to("/timesheet/edit/{$id}")->with('error', "Daily total cannot exceed {$limit} hours (current total for this day: {$dailyTotal} h).");
        }

        $timeEntryModel->update($id, [
            'hours'       => $hours,
            'description' => $description ?: null,
            'is_rework'   => $isRework ? 1 : 0,
        ]);

        return redirect()->to('/timesheet?date=' . urlencode($entry['work_date']))->with('success', 'Time entry updated successfully.');
    }

    /**
     * Log new time entry.
     */
    public function log()
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to('/timesheet');
        }

        $rules = [
            'task_id'   => 'required|integer',
            'work_date' => 'required|valid_date',
            'hours'     => 'required|decimal|greater_than[0]|less_than_equal_to[24]',
        ];
        if (!$this->validate($rules)) {
            $errMsg = implode(' ', $this->validator->getErrors());
            log_message('error', 'Time entry validation failed: ' . $errMsg);
            return redirect()->to('/timesheet?err=' . urlencode($errMsg));
        }

        $taskId = (int) $this->request->getPost('task_id');
        $workDate = $this->request->getPost('work_date');
        $hours = (float) $this->request->getPost('hours');
        $isRework = (bool) $this->request->getPost('is_rework');

        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);
        if (!$task) {
            return redirect()->to('/timesheet?err=' . urlencode('Invalid task.'));
        }
        $productModel = new \App\Models\ProductModel();
        $product = $task['product_id'] ? $productModel->find($task['product_id']) : null;
        $isLeave = $product && ($product['product_type'] ?? null) === 'leave';
        $isAssigned = $task['assignee_id'] !== null && (int) $task['assignee_id'] === $userId;
        if (!$isLeave && !$isAssigned) {
            log_message('error', "Time entry task check failed: taskId={$taskId}, userId={$userId}");
            return redirect()->to('/timesheet?err=' . urlencode('Invalid task or not assigned to you.'));
        }
        if (!empty($task['locked'])) {
            return redirect()->to('/timesheet?err=' . urlencode('Cannot log time for locked/completed task.'));
        }

        $configService = new ConfigService();
        $dailyTotal = (new TimeEntryModel())->getDailyTotal($userId, $workDate);
        $limit = min(24.0, $configService->getDailyHoursLimit());
        if ($dailyTotal + $hours > $limit) {
            return redirect()->to('/timesheet?err=' . urlencode("Daily total cannot exceed {$limit} hours for one day (current total: {$dailyTotal} h)."));
        }

        $timeEntryModel = new TimeEntryModel();
        $description = trim((string) $this->request->getPost('description'));

        $insertData = [
            'task_id'     => $taskId,
            'user_id'     => $userId,
            'work_date'   => $workDate,
            'hours'       => $hours,
            'description' => $description ?: null,
            'is_rework'   => $isRework ? 1 : 0,
            'status'      => 'pending_approval',
        ];

        $insertId = $timeEntryModel->insert($insertData);
        if ($insertId === false) {
            $errors = $timeEntryModel->errors();
            $db = \Config\Database::connect();
            $dbError = $db->error();
            $errMsg = !empty($errors) ? implode(' ', $errors) : ($dbError['message'] ?? 'Database error');
            log_message('error', 'Time entry insert failed: ' . ($errMsg ?: 'unknown') . ' | Data: ' . json_encode($insertData));
            return redirect()->to('/timesheet?err=' . urlencode('Failed to save: ' . $errMsg));
        }

        $redirectUrl = '/timesheet?saved=1&date=' . urlencode($workDate);
        return redirect()->to($redirectUrl)->with('success', 'Timesheet submitted successfully! Your time entry has been recorded.');
    }
}
