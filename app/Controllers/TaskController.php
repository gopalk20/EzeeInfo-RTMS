<?php

namespace App\Controllers;

use App\Libraries\SmartyEngine;
use App\Models\TaskModel;
use App\Models\ProductModel;
use App\Models\UserModel;

class TaskController extends BaseController
{
    public function index()
    {
        $session = session();
        $userId = (int) $session->get('user_id');
        $userRole = $session->get('user_role');

        $user = (new UserModel())->find($userId);
        $userTeamId = isset($user['team_id']) && $user['team_id'] !== '' ? (int) $user['team_id'] : null;

        $productModel = new ProductModel();
        $products = $productModel->getProductsForUser($userId, $userRole, $userTeamId);
        $productIds = array_column($products, 'id');

        $taskModel = new TaskModel();
        $tasks = $taskModel->getTasksForUser($userId, $userRole, $userTeamId, $productIds);

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
