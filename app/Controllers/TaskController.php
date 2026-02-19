<?php

namespace App\Controllers;

use App\Libraries\SmartyEngine;
use App\Models\TaskModel;

class TaskController extends BaseController
{
    public function index()
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        $taskModel = new TaskModel();
        $tasks = $taskModel->getByAssignee($userId);

        $smarty = new SmartyEngine();
        return $smarty->render('tasks/list.tpl', [
            'title'          => 'My Tasks',
            'tasks'          => $tasks,
            'user_email'     => $session->get('user_email'),
            'user_role'      => $session->get('user_role'),
            'is_super_admin' => $session->get('user_role') === 'Super Admin',
        ]);
    }
}
