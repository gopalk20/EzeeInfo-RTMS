<?php

namespace App\Controllers;

use App\Libraries\GitHubService;
use App\Libraries\SmartyEngine;
use App\Models\ProductModel;
use App\Models\TaskModel;
use App\Models\TimeEntryModel;
use App\Models\UserModel;

class ProductController extends BaseController
{
    public function index()
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        $productModel = new ProductModel();
        $products = $productModel->getProductsForUser($userId);

        $smarty = new SmartyEngine();
        return $smarty->render('products/list.tpl', [
            'title'          => 'Products',
            'products'       => $products,
            'user_email'     => $session->get('user_email'),
            'user_role'      => $session->get('user_role'),
            'is_super_admin' => $session->get('user_role') === 'Super Admin',
        ]);
    }

    public function view($id)
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        $productModel = new ProductModel();
        $product = $productModel->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }
        $userProducts = $productModel->getProductsForUser($userId);
        $canView = false;
        foreach ($userProducts as $p) {
            if ((int) $p['id'] === (int) $id) {
                $canView = true;
                break;
            }
        }
        if (!$canView) {
            return redirect()->to('/products')->with('error', 'Access denied.');
        }

        $taskModel = new TaskModel();
        $tasks = $taskModel->select('tasks.*, u.email as assignee_email')
            ->join('users u', 'u.id = tasks.assignee_id', 'left')
            ->where('tasks.product_id', $id)
            ->orderBy('tasks.created_at', 'DESC')
            ->findAll();

        $productModel2 = new ProductModel();
        $members = $productModel2->getMembers($id);
        $allUsers = (new UserModel())->findAll();

        $smarty = new SmartyEngine();
        return $smarty->render('products/view.tpl', [
            'title'          => $product['name'] ?? 'Product',
            'product'        => $product,
            'tasks'          => $tasks,
            'members'        => $members,
            'all_users'      => $allUsers,
            'user_email'     => $session->get('user_email'),
            'user_role'      => $session->get('user_role'),
            'is_super_admin' => $session->get('user_role') === 'Super Admin',
            'can_manage_tasks'=> in_array($session->get('user_role'), ['Manager', 'Super Admin'], true),
            'success'        => $session->getFlashdata('success'),
            'error'          => $session->getFlashdata('error'),
            'csrf'           => csrf_token(),
            'hash'           => csrf_hash(),
        ]);
    }

    public function taskAdd(int $productId)
    {
        $session = session();
        if (!in_array($session->get('user_role'), ['Manager', 'Super Admin'], true)) {
            return redirect()->to("/products/view/{$productId}")->with('error', 'Access denied.');
        }
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to("/products/view/{$productId}");
        }
        $rules = ['title' => 'required|max_length[512]'];
        if (!$this->validate($rules)) {
            return redirect()->to("/products/view/{$productId}")->with('error', 'Task title is required.');
        }
        $productModel = new ProductModel();
        $product = $productModel->find($productId);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }
        $taskModel = new TaskModel();
        $taskModel->insert([
            'product_id'   => $productId,
            'title'        => trim($this->request->getPost('title')),
            'status'       => $this->request->getPost('status') ?: 'To Do',
            'assignee_id'  => $this->request->getPost('assignee_id') ?: null,
        ]);
        return redirect()->to("/products/view/{$productId}")->with('success', 'Task added successfully.');
    }

    public function taskEdit(int $productId, int $taskId)
    {
        $session = session();
        if (!in_array($session->get('user_role'), ['Manager', 'Super Admin'], true)) {
            return redirect()->to("/products/view/{$productId}")->with('error', 'Access denied.');
        }
        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);
        if (!$task || (int) $task['product_id'] !== $productId) {
            return redirect()->to("/products/view/{$productId}")->with('error', 'Task not found.');
        }
        if ($this->request->getMethod() === 'post') {
            $rules = ['title' => 'required|max_length[512]'];
            if (!$this->validate($rules)) {
                return redirect()->to("/products/view/{$productId}")->with('error', 'Task title is required.');
            }
            $taskModel->update($taskId, [
                'title'       => trim($this->request->getPost('title')),
                'status'      => $this->request->getPost('status') ?: 'To Do',
                'assignee_id' => $this->request->getPost('assignee_id') ?: null,
            ]);
            return redirect()->to("/products/view/{$productId}")->with('success', 'Task updated successfully.');
        }
        $productModel = new ProductModel();
        $product = $productModel->find($productId);
        $allUsers = (new UserModel())->findAll();
        $smarty = new SmartyEngine();
        return $smarty->render('products/task_edit.tpl', [
            'title'     => 'Edit Task',
            'product'   => $product,
            'task'      => $task,
            'all_users' => $allUsers,
            'user_email' => $session->get('user_email'),
            'csrf'      => csrf_token(),
            'hash'      => csrf_hash(),
        ]);
    }

    public function taskDelete(int $productId, int $taskId)
    {
        $session = session();
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to("/products/view/{$productId}");
        }
        if (!in_array($session->get('user_role'), ['Manager', 'Super Admin'], true)) {
            return redirect()->to("/products/view/{$productId}")->with('error', 'Access denied.');
        }
        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);
        if (!$task || (int) $task['product_id'] !== $productId) {
            return redirect()->to("/products/view/{$productId}")->with('error', 'Task not found.');
        }
        $timeEntryModel = new TimeEntryModel();
        $entries = $timeEntryModel->getByTask($taskId);
        if (!empty($entries)) {
            return redirect()->to("/products/view/{$productId}")->with('error', 'Cannot delete: time entries are mapped to this task.');
        }
        $taskModel->delete($taskId);
        return redirect()->to("/products/view/{$productId}")->with('success', 'Task deleted successfully.');
    }

    public function syncFromGitHub($id)
    {
        $session = session();
        $userId = (int) $session->get('user_id');
        $role = $session->get('user_role');

        if (!in_array($role, ['Product Lead', 'Manager', 'Super Admin'], true)) {
            return redirect()->to('/products')->with('error', 'Only Product Lead or Manager can sync.');
        }

        $productModel = new ProductModel();
        $product = $productModel->find($id);
        if (!$product || empty($product['github_repo_url'])) {
            return redirect()->to("/products/view/{$id}")->with('error', 'Product or GitHub URL not set.');
        }

        $github = new GitHubService();
        $parsed = $github->parseRepoUrl($product['github_repo_url']);
        if (!$parsed) {
            return redirect()->to("/products/view/{$id}")->with('error', 'Invalid GitHub URL.');
        }

        $issues = $github->fetchIssues($parsed['owner'], $parsed['repo']);
        if (empty($issues)) {
            return redirect()->to("/products/view/{$id}")->with('success', 'No open issues, or GitHub API error (check GITHUB_PAT in .env).');
        }

        $taskModel = new TaskModel();
        $created = 0;
        foreach ($issues as $issue) {
            $title = $issue['title'] ?? '';
            $githubId = (string) ($issue['number'] ?? '');
            if (empty($title)) {
                continue;
            }
            $existing = $taskModel->where('product_id', $id)->where('github_issue_id', $githubId)->first();
            if (!$existing) {
                $taskModel->insert([
                    'product_id'      => $id,
                    'github_issue_id' => $githubId,
                    'title'           => $title,
                    'status'          => 'To Do',
                ]);
                $created++;
            }
        }

        return redirect()->to("/products/view/{$id}")->with('success', "Synced {$created} new tasks from GitHub.");
    }
}
