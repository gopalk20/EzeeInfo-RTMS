<?php

namespace App\Controllers;

use App\Libraries\SmartyEngine;
use App\Models\TimeEntryModel;
use App\Models\TaskModel;
use App\Models\UserModel;

class Home extends BaseController
{
    /**
     * Display dashboard
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

        return $smarty->render('home.tpl', $data);
    }
}
