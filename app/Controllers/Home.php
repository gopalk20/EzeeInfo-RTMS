<?php

namespace App\Controllers;

use App\Libraries\ConfigService;
use App\Libraries\SmartyEngine;
use App\Models\ResourceCostModel;
use App\Models\TimeEntryModel;
use App\Models\TaskModel;
use App\Models\UserModel;

class Home extends BaseController
{
    /**
     * Unified dashboard (FR-040, Q13.11–Q13.12).
     * Role-based content: Employee/Finance/Product Lead see cards; Manager/Super Admin see cards + admin widgets.
     */
    public function index(): string
    {
        $smarty = new SmartyEngine();
        $session = session();
        $userId = (int) $session->get('user_id');
        $userRole = $session->get('user_role');
        $userModel = new UserModel();
        $user = $session->get('user') ? $userModel->find($session->get('user_id')) : null;
        $displayName = $user ? $userModel->getDisplayName($user) : $session->get('user_email');

        $pendingCount = 0;
        if (in_array($userRole, ['Manager', 'Product Lead', 'Super Admin'])) {
            $timeEntryModel = new TimeEntryModel();
            $pendingEntries = $timeEntryModel->getPendingForApprover($userId, $userRole);
            $pendingTasks = \Config\Database::connect()->table('tasks')
                ->where('status', 'Completed')->where('locked', 0)->get()->getResultArray();
            $pendingCount = count($pendingEntries) + count($pendingTasks);
        }

        $myTaskCount = 0;
        if ($userId) {
            $myTaskCount = (new TaskModel())->where('assignee_id', $userId)->countAllResults();
        }

        $data = [
            'title'          => 'Dashboard',
            'nav_active'     => 'home',
            'user_email'     => $session->get('user_email'),
            'user_role'      => $userRole,
            'display_name'   => $displayName,
            'is_super_admin'=> $userRole === 'Super Admin',
            'pending_count'  => $pendingCount,
            'my_task_count'  => $myTaskCount,
            'success'        => $session->getFlashdata('success'),
        ];

        $showAdminWidgets = in_array($userRole, ['Manager', 'Super Admin'], true);
        if ($showAdminWidgets) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
            $timeEntryModel = new TimeEntryModel();
            $configService = new ConfigService();
            $costModel = new ResourceCostModel();
            $overall = $timeEntryModel->getBillableNonBillableHours($from, $to);
            $monthlyHours = $timeEntryModel->getMonthlyHoursSummary(7);
            $hoursByProduct = $timeEntryModel->getHoursByProduct($from, $to);
            $pendingApprovers = $timeEntryModel->getPendingApproversList();
            $totalRevenue = 0.0;
            $pendingBillableHours = 0.0;
            $rows = \Config\Database::connect()->query("
                SELECT te.user_id, SUM(te.hours) as total_hours
                FROM time_entries te
                JOIN tasks ON tasks.id = te.task_id
                JOIN products ON products.id = tasks.product_id
                WHERE te.status = 'approved'
                    AND te.work_date >= ? AND te.work_date <= ?
                    AND (products.product_type IS NULL OR products.product_type != 'leave')
                GROUP BY te.user_id
            ", [$from, $to])->getResultArray();
            foreach ($rows as $r) {
                $costRow = $costModel->getForUser($r['user_id']);
                $monthlyCost = $costRow ? (float) $costRow['monthly_cost'] : 0;
                $hourlyCost = $configService->calculateHourlyCost($monthlyCost);
                $hrs = (float) $r['total_hours'];
                $totalRevenue += $hrs * $hourlyCost;
                $pendingBillableHours += $hrs;
            }
            $data['show_admin_widgets'] = true;
            $data['overall_hours'] = $overall;
            $data['monthly_hours'] = $monthlyHours;
            $data['hours_by_product'] = $hoursByProduct;
            $data['pending_approvers'] = $pendingApprovers;
            $data['total_revenue'] = $totalRevenue;
            $data['pending_hours'] = $pendingBillableHours;
            $data['pending_invoices'] = 0;
            $data['pending_payments'] = 0.0;
            $data['from'] = $from;
            $data['to'] = $to;
        } else {
            $data['show_admin_widgets'] = false;
        }

        return $smarty->render('home.tpl', $data);
    }
}
