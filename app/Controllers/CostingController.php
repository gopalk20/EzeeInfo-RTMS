<?php

namespace App\Controllers;

use App\Libraries\ConfigService;
use App\Libraries\SmartyEngine;
use App\Models\ResourceCostModel;
use App\Models\UserModel;

class CostingController extends BaseController
{
    protected $helpers = ['form'];

    /**
     * User costing VS Project costing display.
     * User cost is configured in Manage Users > Edit User (Super Admin only).
     */
    public function index()
    {
        $session = session();
        $configService = new ConfigService();
        $costModel = new ResourceCostModel();
        $workingDays = $configService->getWorkingDays();
        $standardHours = $configService->getStandardHours();

        $from = $this->request->getGet('from');
        $to = $this->request->getGet('to');
        if (!$from || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = date('Y-m-01');
        }
        if (!$to || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = date('Y-m-t');
        }
        if (strtotime($from) > strtotime($to)) {
            $to = $from;
        }

        $db = \Config\Database::connect();

        // User costing: users with time entries in period, their hours and cost
        $userCosting = $db->query("
            SELECT u.id, u.email, u.first_name, u.last_name,
                   SUM(te.hours) as total_hours
            FROM users u
            JOIN time_entries te ON te.user_id = u.id
                AND te.work_date >= ? AND te.work_date <= ?
            GROUP BY u.id, u.email, u.first_name, u.last_name
            ORDER BY u.email
        ", [$from, $to])->getResultArray();

        foreach ($userCosting as &$r) {
            $hrs = (float) ($r['total_hours'] ?? 0);
            $costRow = $costModel->getForUser((int) $r['id']);
            $monthly = $costRow ? (float) $costRow['monthly_cost'] : 0;
            $hourly = ($workingDays > 0 && $standardHours > 0)
                ? $monthly / ($workingDays * $standardHours)
                : 0;
            $r['hourly_cost'] = round($hourly, 2);
            $r['period_cost'] = round($hrs * $hourly, 2);
            $r['monthly_cost'] = $monthly;
        }

        // Project costing: products with time entries, total hours and cost
        $projectCosting = $db->query("
            SELECT p.id, p.name as product_name,
                   SUM(te.hours) as total_hours
            FROM products p
            JOIN tasks t ON t.product_id = p.id
            JOIN time_entries te ON te.task_id = t.id
                AND te.work_date >= ? AND te.work_date <= ?
            WHERE (p.product_type IS NULL OR p.product_type != 'leave')
            GROUP BY p.id, p.name
            ORDER BY p.name
        ", [$from, $to])->getResultArray();

        foreach ($projectCosting as &$r) {
            $productId = (int) $r['id'];
            $costRows = $db->query("
                SELECT te.user_id, SUM(te.hours) as hours
                FROM time_entries te
                JOIN tasks t ON t.id = te.task_id
                WHERE t.product_id = ? AND te.work_date >= ? AND te.work_date <= ?
                GROUP BY te.user_id
            ", [$productId, $from, $to])->getResultArray();
            $totalCost = 0.0;
            foreach ($costRows as $cr) {
                $costRow = $costModel->getForUser((int) $cr['user_id']);
                $monthly = $costRow ? (float) $costRow['monthly_cost'] : 0;
                $hourly = ($workingDays > 0 && $standardHours > 0)
                    ? $monthly / ($workingDays * $standardHours)
                    : 0;
                $totalCost += (float) $cr['hours'] * $hourly;
            }
            $r['period_cost'] = round($totalCost, 2);
        }

        $smarty = new SmartyEngine();
        return $smarty->render('costing/index.tpl', [
            'title'           => 'Resource Costing',
            'user_costing'    => $userCosting,
            'project_costing' => $projectCosting,
            'from'            => $from,
            'to'              => $to,
            'working_days'    => $workingDays,
            'standard_hours'  => $standardHours,
            'user_email'      => $session->get('user_email'),
            'user_role'       => $session->get('user_role'),
            'nav_active'      => 'costing',
        ]);
    }
}
