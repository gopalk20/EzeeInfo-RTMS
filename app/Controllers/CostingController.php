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
     * List resource costs; Manager-only.
     */
    public function index()
    {
        $session = session();
        $costModel = new ResourceCostModel();
        $configService = new ConfigService();
        $workingDays = $configService->getWorkingDays();
        $standardHours = $configService->getStandardHours();

        $costs = $costModel->getAllWithUsers();
        $userModel = new UserModel();
        $users = $userModel->select('id, email, first_name, last_name')->findAll();

        foreach ($costs as &$c) {
            $monthly = (float) ($c['monthly_cost'] ?? 0);
            $c['hourly_cost'] = ($workingDays > 0 && $standardHours > 0)
                ? round($monthly / ($workingDays * $standardHours), 2)
                : 0;
        }

        $smarty = new SmartyEngine();
        return $smarty->render('costing/index.tpl', [
            'title'          => 'Resource Costing',
            'costs'          => $costs,
            'users'          => $users,
            'working_days'   => $workingDays,
            'standard_hours' => $standardHours,
            'user_email'     => $session->get('user_email'),
            'user_role'      => $session->get('user_role'),
            'is_super_admin'=> $session->get('user_role') === 'Super Admin',
            'success'        => $session->getFlashdata('success'),
            'error'          => $session->getFlashdata('error'),
            'csrf'           => csrf_token(),
            'hash'           => csrf_hash(),
        ]);
    }

    public function save()
    {
        $session = session();
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/costing');
        }

        $userId = (int) $this->request->getPost('user_id');
        $monthlyCost = (float) $this->request->getPost('monthly_cost');

        if ($userId <= 0) {
            return redirect()->to('/costing')->with('error', 'Invalid user.');
        }

        $costModel = new ResourceCostModel();
        $existing = $costModel->where('user_id', $userId)->first();
        if ($existing) {
            $costModel->update($existing['id'], [
                'monthly_cost' => $monthlyCost,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        } else {
            $costModel->insert([
                'user_id'      => $userId,
                'monthly_cost' => $monthlyCost,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/costing')->with('success', 'Cost saved.');
    }
}
