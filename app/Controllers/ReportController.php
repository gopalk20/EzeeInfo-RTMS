<?php

namespace App\Controllers;

use App\Libraries\ConfigService;
use App\Libraries\SmartyEngine;
use App\Models\ResourceCostModel;
use App\Models\TimeEntryModel;
use App\Models\TaskModel;

class ReportController extends BaseController
{
    public function index()
    {
        $session = session();
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
        $smarty = new SmartyEngine();
        return $smarty->render('reports/index.tpl', [
            'title'          => 'Reports',
            'nav_active'     => 'reports',
            'from'           => $from,
            'to'             => $to,
            'user_email'     => $session->get('user_email'),
            'user_role'      => $session->get('user_role'),
            'is_super_admin' => $session->get('user_role') === 'Super Admin',
        ]);
    }

    /**
     * Parse from/to date params, default to current month.
     */
    protected function getReportDateRange(): array
    {
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
        return [$from, $to];
    }

    /**
     * Task-wise time report. Finance sees time/rework only; Manager sees costing.
     */
    public function taskWise()
    {
        $session = session();
        $role = $session->get('user_role');
        $showCosting = in_array($role, ['Manager', 'Super Admin'], true);

        [$from, $to] = $this->getReportDateRange();

        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT t.id, t.title, t.status, p.name as product_name,
                   SUM(te.hours) as total_hours,
                   SUM(CASE WHEN te.is_rework = 1 THEN te.hours ELSE 0 END) as rework_hours
            FROM tasks t
            JOIN products p ON p.id = t.product_id
            LEFT JOIN time_entries te ON te.task_id = t.id AND te.work_date >= ? AND te.work_date <= ?
            GROUP BY t.id, t.title, t.status, p.name
            HAVING SUM(te.hours) > 0
            ORDER BY p.name, t.title
        ", [$from, $to])->getResultArray();

        foreach ($rows as &$r) {
            $total = (float) ($r['total_hours'] ?? 0);
            $rework = (float) ($r['rework_hours'] ?? 0);
            $r['rework_pct'] = $total > 0 ? round(($rework / $total) * 100, 1) : 0;
        }

        $smarty = new SmartyEngine();
        return $smarty->render('reports/task_wise.tpl', [
            'title'        => 'Task-wise Time Report',
            'rows'         => $rows,
            'from'         => $from,
            'to'           => $to,
            'show_costing' => $showCosting,
            'user_email'   => $session->get('user_email'),
            'user_role'    => $role,
            'is_super_admin' => $role === 'Super Admin',
        ]);
    }

    /**
     * Employee-wise time report.
     */
    public function employeeWise()
    {
        $session = session();
        $role = $session->get('user_role');
        $showCosting = in_array($role, ['Manager', 'Super Admin'], true);

        [$from, $to] = $this->getReportDateRange();

        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT u.id, u.email, u.first_name, u.last_name,
                   SUM(te.hours) as total_hours,
                   SUM(CASE WHEN te.is_rework = 1 THEN te.hours ELSE 0 END) as rework_hours
            FROM users u
            LEFT JOIN time_entries te ON te.user_id = u.id AND te.work_date >= ? AND te.work_date <= ?
            GROUP BY u.id, u.email, u.first_name, u.last_name
            HAVING SUM(te.hours) > 0
            ORDER BY u.email
        ", [$from, $to])->getResultArray();

        $configService = new ConfigService();
        $costModel = new ResourceCostModel();
        foreach ($rows as &$r) {
            $total = (float) ($r['total_hours'] ?? 0);
            $rework = (float) ($r['rework_hours'] ?? 0);
            $r['rework_pct'] = $total > 0 ? round(($rework / $total) * 100, 1) : 0;
            if ($showCosting) {
                $costRow = $costModel->getForUser((int) $r['id']);
                $monthly = $costRow ? (float) $costRow['monthly_cost'] : 0;
                $hourly = $configService->calculateHourlyCost($monthly);
                $r['cost'] = round($total * $hourly, 2);
                $r['hourly_cost'] = $hourly;
            } else {
                $r['cost'] = null;
                $r['hourly_cost'] = null;
            }
        }

        $smarty = new SmartyEngine();
        return $smarty->render('reports/employee_wise.tpl', [
            'title'         => 'Employee-wise Time Report',
            'rows'          => $rows,
            'from'          => $from,
            'to'            => $to,
            'show_costing'  => $showCosting,
            'user_email'    => $session->get('user_email'),
            'user_role'     => $role,
            'is_super_admin'=> $role === 'Super Admin',
        ]);
    }

    /**
     * Performance / rework impact report.
     */
    public function performance()
    {
        $session = session();

        [$from, $to] = $this->getReportDateRange();

        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT u.email, u.first_name, u.last_name,
                   SUM(te.hours) as total_hours,
                   SUM(CASE WHEN te.is_rework = 1 THEN te.hours ELSE 0 END) as rework_hours,
                   COUNT(DISTINCT te.task_id) as task_count
            FROM users u
            LEFT JOIN time_entries te ON te.user_id = u.id AND te.work_date >= ? AND te.work_date <= ?
            GROUP BY u.id, u.email, u.first_name, u.last_name
            HAVING SUM(te.hours) > 0
            ORDER BY rework_hours DESC
        ", [$from, $to])->getResultArray();

        foreach ($rows as &$r) {
            $total = (float) ($r['total_hours'] ?? 0);
            $rework = (float) ($r['rework_hours'] ?? 0);
            $r['rework_pct'] = $total > 0 ? round(($rework / $total) * 100, 1) : 0;
        }

        $smarty = new SmartyEngine();
        return $smarty->render('reports/performance.tpl', [
            'title'          => 'Performance & Rework',
            'rows'           => $rows,
            'from'           => $from,
            'to'             => $to,
            'user_email'     => $session->get('user_email'),
            'user_role'      => $session->get('user_role'),
            'is_super_admin' => $session->get('user_role') === 'Super Admin',
        ]);
    }

    /**
     * Export task-wise report as CSV.
     */
    public function exportTaskWise()
    {
        $session = session();
        $role = $session->get('user_role');
        if (!in_array($role, ['Finance', 'Manager', 'Super Admin'], true)) {
            return redirect()->to('/reports')->with('error', 'Access denied.');
        }

        [$from, $to] = $this->getReportDateRange();

        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT t.title, p.name as product_name, t.status,
                   SUM(te.hours) as total_hours,
                   SUM(CASE WHEN te.is_rework = 1 THEN te.hours ELSE 0 END) as rework_hours
            FROM tasks t
            JOIN products p ON p.id = t.product_id
            LEFT JOIN time_entries te ON te.task_id = t.id AND te.work_date >= ? AND te.work_date <= ?
            GROUP BY t.id
            HAVING SUM(te.hours) > 0
            ORDER BY p.name, t.title
        ", [$from, $to])->getResultArray();

        $csv = "Task,Product,Status,Total Hours,Rework Hours,Rework %\n";
        foreach ($rows as $r) {
            $total = (float) ($r['total_hours'] ?? 0);
            $rework = (float) ($r['rework_hours'] ?? 0);
            $pct = $total > 0 ? round(($rework / $total) * 100, 1) : 0;
            $csv .= sprintf("%s,%s,%s,%.2f,%.2f,%.1f\n",
                $this->csvEscape($r['title'] ?? ''),
                $this->csvEscape($r['product_name'] ?? ''),
                $r['status'] ?? '',
                $total, $rework, $pct
            );
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="task_wise_report_' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }

    protected function csvEscape(string $s): string
    {
        if (strpos($s, ',') !== false || strpos($s, '"') !== false) {
            return '"' . str_replace('"', '""', $s) . '"';
        }
        return $s;
    }
}
