<?php

namespace App\Controllers;

use App\Libraries\ConfigService;
use App\Libraries\SmartyEngine;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\TeamModel;
use App\Models\ProductModel;
use App\Models\TimeEntryModel;
use App\Models\ResourceCostModel;

class AdminController extends BaseController
{
    protected $helpers = ['form'];

    /**
     * Admin dashboard: Overall Hours, Work Hours Summary, Resource Allocation, Pending Approvers, Financial Summary.
     * Available to Manager and Super Admin.
     */
    public function dashboard()
    {
        $session = session();
        $userRole = $session->get('user_role');
        if (!in_array($userRole, ['Manager', 'Super Admin'], true)) {
            return redirect()->to('/home');
        }

        $userId = (int) $session->get('user_id');
        $from = date('Y-m-01');
        $to = date('Y-m-t');
        $timeEntryModel = new TimeEntryModel();

        $pendingCount = 0;
        $pendingEntries = $timeEntryModel->getPendingForApprover($userId, $userRole);
        $pendingTasks = \Config\Database::connect()->table('tasks')
            ->where('status', 'Completed')->where('locked', 0)->countAllResults();
        $pendingCount = count($pendingEntries) + $pendingTasks;
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

        $smarty = new SmartyEngine();
        return $smarty->render('admin/dashboard.tpl', [
            'title'             => 'Admin Dashboard',
            'pending_count'     => $pendingCount,
            'nav_active'        => 'admin_dashboard',
            'overall_hours'     => $overall,
            'monthly_hours'     => $monthlyHours,
            'hours_by_product'  => $hoursByProduct,
            'pending_approvers' => $pendingApprovers,
            'total_revenue'     => $totalRevenue,
            'pending_hours'     => $pendingBillableHours,
            'pending_invoices'  => 0,
            'pending_payments'  => 0.0,
            'from'              => $from,
            'to'                => $to,
            'user_email'        => $session->get('user_email'),
            'user_role'         => $userRole,
            'is_super_admin'    => $userRole === 'Super Admin',
        ]);
    }

    public function users()
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();
        $teamModel = new TeamModel();

        $teamFilter = $this->request->getGet('team');
        $search = trim((string) $this->request->getGet('q'));
        $sort = $this->request->getGet('sort') ?: 'name';
        $dir = strtolower((string) ($this->request->getGet('dir') ?: 'asc'));
        if ($dir !== 'desc') {
            $dir = 'asc';
        }

        $builder = $userModel->select('users.*, rm.first_name as rm_first, rm.last_name as rm_last')
            ->join('users as rm', 'rm.id = users.reporting_manager_id', 'left');
        if ($teamFilter !== null && $teamFilter !== '') {
            $builder->join('teams', 'teams.id = users.team_id', 'inner')
                ->where('teams.name', $teamFilter);
        }
        if ($search !== '') {
            $builder->groupStart()
                ->like('users.email', $search)
                ->orLike('users.first_name', $search)
                ->orLike('users.last_name', $search)
                ->orLike('users.username', $search)
                ->groupEnd();
        }
        $sortCol = match ($sort) {
            'id' => 'users.id',
            'email' => 'users.email',
            'role' => 'users.role_id',
            'team' => 'users.team_id',
            'created' => 'users.created_at',
            default => 'users.first_name',
        };
        $builder->orderBy($sortCol, $dir);
        $users = $builder->findAll();

        foreach ($users as &$u) {
            $u['role_name'] = ($userModel->getRole($u)['name'] ?? '—');
            $u['team_name'] = ($userModel->getTeam($u)['name'] ?? '—');
            $u['reporting_manager_name'] = trim(($u['rm_first'] ?? '') . ' ' . ($u['rm_last'] ?? '')) ?: '—';
            $u['display_name'] = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: ($u['email'] ?? '—');
            $u['created_at_fmt'] = ! empty($u['created_at']) ? date('d-m-Y', strtotime($u['created_at'])) : '—';
        }
        $teams = $teamModel->orderBy('name')->findAll();

        $smarty = new SmartyEngine();
        return $smarty->render('admin/users.tpl', [
            'title'         => 'Manage Users',
            'nav_active'    => 'users',
            'users'         => $users,
            'teams'         => $teams,
            'filter_team'   => $teamFilter ?? '',
            'search'        => $search,
            'sort'          => $sort,
            'dir'           => $dir,
            'user_email'    => session()->get('user_email'),
            'user_role'     => session()->get('user_role'),
            'is_super_admin'=> true,
            'success'       => session()->getFlashdata('success'),
            'error'         => session()->getFlashdata('error'),
            'csrf'          => csrf_token(),
            'hash'          => csrf_hash(),
        ]);
    }

    public function addUser()
    {
        $roleModel = new RoleModel();
        $teamModel = new TeamModel();

        if (strtolower((string) $this->request->getMethod()) === 'post') {
            $rules = [
                'username'   => 'required|max_length[64]',
                'email'      => 'required|valid_email|is_unique[users.email]',
                'first_name' => 'required|max_length[128]',
                'last_name'  => 'required|max_length[128]',
                'password'   => 'required|min_length[8]',
                'role_id'    => 'required|integer',
                'team_id'    => 'required|integer',
                'phone'      => 'permit_empty|max_length[32]',
            ];
            if (!$this->validate($rules)) {
                return $this->addUserForm($this->validator->getErrors());
            }

            $userModel = new UserModel();
            $userModel->insert([
                'username'   => $this->request->getPost('username'),
                'email'      => $this->request->getPost('email'),
                'first_name' => $this->request->getPost('first_name'),
                'last_name'  => $this->request->getPost('last_name'),
                'password'   => $this->request->getPost('password'),
                'role_id'    => (int) $this->request->getPost('role_id'),
                'team_id'    => (int) $this->request->getPost('team_id'),
                'phone'      => $this->request->getPost('phone') ?: null,
                'name'       => $this->request->getPost('first_name') . ' ' . $this->request->getPost('last_name'),
                'is_active'  => 1,
            ]);

            return redirect()->to('/admin/users')->with('success', 'User added successfully.');
        }

        return $this->addUserForm([]);
    }

    protected function addUserForm(array $errors): string
    {
        $roleModel = new RoleModel();
        $teamModel = new TeamModel();
        $roles = $roleModel->findAll();
        $teams = $teamModel->findAll();

        $smarty = new SmartyEngine();
        return $smarty->render('admin/add_user.tpl', [
            'title'         => 'Add User',
            'roles'         => $roles,
            'teams'         => $teams,
            'errors'        => $errors,
            'csrf'          => csrf_token(),
            'hash'          => csrf_hash(),
            'user_email'    => session()->get('user_email'),
            'user_role'     => session()->get('user_role'),
            'is_super_admin'=> true,
        ]);
    }

    public function userEdit(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        if (strtolower((string) $this->request->getMethod()) === 'post') {
            $reportingManagerId = $this->request->getPost('reporting_manager_id');
            $isActive = $this->request->getPost('is_active');
            $newPassword = $this->request->getPost('new_password');

            $updates = [];
            $updates['reporting_manager_id'] = ($reportingManagerId === '' || $reportingManagerId === null) ? null : (int) $reportingManagerId;
            $updates['is_active'] = in_array($isActive, ['1', 'on'], true) ? 1 : 0;

            if ($newPassword !== null && $newPassword !== '') {
                $rules = [
                    'new_password'     => 'required|min_length[8]',
                    'confirm_password'=> 'required|matches[new_password]',
                ];
                if (!$this->validate($rules)) {
                    return redirect()->back()->withInput()->with('error', 'Password must be at least 8 characters and must match.');
                }
                $updates['password'] = $newPassword;
            }

            $userModel->skipValidation(true)->update($id, $updates);

            if (session()->get('user_role') === 'Super Admin') {
                $monthlyCost = (float) ($this->request->getPost('monthly_cost') ?? 0);
                $costModel = new ResourceCostModel();
                $existing = $costModel->where('user_id', $id)->first();
                if ($existing) {
                    $costModel->update($existing['id'], [
                        'monthly_cost' => $monthlyCost,
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $costModel->insert([
                        'user_id'      => $id,
                        'monthly_cost' => $monthlyCost,
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $msg = 'User updated successfully.';
            if (!empty($updates['password'])) {
                $msg = 'User updated. Password reset successfully.';
            }
            return redirect()->to('/admin/users')->with('success', $msg);
        }

        $roleModel = new RoleModel();
        $roleIds = $roleModel->whereIn('name', ['Manager', 'Product Lead', 'Super Admin'])->findColumn('id');
        $managers = $roleIds ? $userModel->whereIn('role_id', $roleIds)->findAll() : [];

        $costModel = new ResourceCostModel();
        $costRow = $costModel->getForUser($id);
        $user['monthly_cost'] = $costRow ? (float) $costRow['monthly_cost'] : '';

        $user['role_name'] = ($userModel->getRole($user)['name'] ?? '—');
        $user['team_name'] = ($userModel->getTeam($user)['name'] ?? '—');

        $smarty = new SmartyEngine();
        return $smarty->render('admin/user_edit.tpl', [
            'title'         => 'Edit User',
            'user'          => $user,
            'managers'      => $managers,
            'user_email'    => session()->get('user_email'),
            'user_role'     => session()->get('user_role'),
            'is_super_admin'=> session()->get('user_role') === 'Super Admin',
            'success'       => session()->getFlashdata('success'),
            'error'         => session()->getFlashdata('error'),
            'csrf'          => csrf_token(),
            'hash'          => csrf_hash(),
        ]);
    }

    public function setReportingManager(int $id)
    {
        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/users');
        }
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }
        $managerId = $this->request->getPost('reporting_manager_id');
        $value = $managerId === '' || $managerId === null ? null : (int) $managerId;
        $userModel->skipValidation(true)->update($id, ['reporting_manager_id' => $value]);
        return redirect()->to('/admin/users')->with('success', 'Reporting manager updated.');
    }

    public function toggleActive(int $id)
    {
        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/users');
        }
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }
        $current = (int) ($user['is_active'] ?? 1);
        $new = $current ? 0 : 1;
        $userModel->skipValidation(true)->update($id, ['is_active' => $new]);
        $action = $new ? 'enabled' : 'disabled';
        return redirect()->to('/admin/users')->with('success', "User {$action} successfully.");
    }

    public function resetUserPassword(int $id)
    {
        if (strtolower((string) $this->request->getMethod()) === 'post') {
            $rules = [
                'new_password'     => 'required|min_length[8]',
                'confirm_password' => 'required|matches[new_password]',
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', 'Validation failed. Password must be at least 8 characters.');
            }

            $userModel = new UserModel();
            $user = $userModel->find($id);
            if (!$user) {
                return redirect()->to('/admin/users')->with('error', 'User not found.');
            }

            $userModel->skipValidation(true)->update($id, ['password' => $this->request->getPost('new_password')]);

            return redirect()->to('/admin/users')->with('success', 'Password reset successfully for ' . ($user['email'] ?? 'user') . '.');
        }

        return redirect()->back();
    }

    public function productsManage()
    {
        $productModel = new ProductModel();
        $search = trim((string) $this->request->getGet('q'));
        $sort = $this->request->getGet('sort') ?: 'name';
        $dir = strtolower((string) ($this->request->getGet('dir') ?: 'asc'));
        if ($dir !== 'desc') {
            $dir = 'asc';
        }

        $builder = $productModel->select('products.*, u.email as lead_email, u.first_name as lead_first, u.last_name as lead_last')
            ->join('users u', 'u.id = products.product_lead_id', 'left');
        if ($search !== '') {
            $builder->like('products.name', $search);
        }
        $sortCol = match ($sort) {
            'id' => 'products.id',
            'created' => 'products.created_at',
            default => 'products.name',
        };
        $builder->orderBy($sortCol, $dir);
        $products = $builder->findAll();

        foreach ($products as &$p) {
            $p['lead_name'] = trim(($p['lead_first'] ?? '') . ' ' . ($p['lead_last'] ?? '')) ?: ($p['lead_email'] ?? '—');
            $p['created_at_fmt'] = ! empty($p['created_at']) ? date('d-m-Y', strtotime($p['created_at'])) : '—';
        }

        $smarty = new SmartyEngine();
        return $smarty->render('admin/products_manage.tpl', [
            'title'          => 'Manage Products',
            'nav_active'     => 'products_manage',
            'products'       => $products,
            'search'         => $search,
            'sort'           => $sort,
            'dir'            => $dir,
            'user_email'     => session()->get('user_email'),
            'success'        => session()->getFlashdata('success'),
            'error'          => session()->getFlashdata('error'),
            'csrf'           => csrf_token(),
            'hash'           => csrf_hash(),
        ]);
    }

    public function productAdd()
    {
        if (strtolower((string) $this->request->getMethod()) === 'post') {
            $rules = ['name' => 'required|max_length[255]'];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', 'Product name is required.');
            }
            $productModel = new ProductModel();
            $productModel->insert([
                'name' => trim($this->request->getPost('name')),
            ]);
            return redirect()->to('/admin/products/manage')->with('success', 'Product added successfully.');
        }
        $userModel = new UserModel();
        $roleModel = new RoleModel();
        $leadIds = $roleModel->where('name', 'Product Lead')->findColumn('id');
        $leads = $leadIds ? $userModel->whereIn('role_id', $leadIds)->findAll() : [];

        $smarty = new SmartyEngine();
        return $smarty->render('admin/product_form.tpl', [
            'title'   => 'Add Product',
            'product' => null,
            'leads'   => $leads,
            'user_email' => session()->get('user_email'),
            'csrf'    => csrf_token(),
            'hash'    => csrf_hash(),
        ]);
    }

    public function productEdit(int $id)
    {
        $productModel = new ProductModel();
        $product = $productModel->find($id);
        if (!$product) {
            return redirect()->to('/admin/products/manage')->with('error', 'Product not found.');
        }
        if (strtolower((string) $this->request->getMethod()) === 'post') {
            $rules = ['name' => 'required|max_length[255]'];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', 'Product name is required.');
            }
            $productModel->update($id, [
                'name'             => trim($this->request->getPost('name')),
                'product_lead_id'  => $this->request->getPost('product_lead_id') ?: null,
            ]);
            return redirect()->to('/admin/products/manage')->with('success', 'Product updated successfully.');
        }
        $userModel = new UserModel();
        $roleModel = new RoleModel();
        $leadIds = $roleModel->where('name', 'Product Lead')->findColumn('id');
        $leads = $leadIds ? $userModel->whereIn('role_id', $leadIds)->findAll() : [];
        $members = $productModel->getMembers($id);
        $allUsers = $userModel->findAll();

        $smarty = new SmartyEngine();
        return $smarty->render('admin/product_form.tpl', [
            'title'        => 'Edit Product',
            'product'      => $product,
            'leads'        => $leads,
            'all_users'    => $allUsers,
            'members'      => $members,
            'user_email'   => session()->get('user_email'),
            'success'      => session()->getFlashdata('success'),
            'error'        => session()->getFlashdata('error'),
            'csrf'         => csrf_token(),
            'hash'         => csrf_hash(),
        ]);
    }

    public function productDelete(int $id)
    {
        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/products/manage');
        }
        $productModel = new ProductModel();
        $product = $productModel->find($id);
        if (!$product) {
            return redirect()->to('/admin/products/manage')->with('error', 'Product not found.');
        }
        $members = $productModel->getMembers($id);
        $taskCount = $productModel->db->table('tasks')->where('product_id', $id)->countAllResults();
        if (!empty($members) || $taskCount > 0) {
            return redirect()->to('/admin/products/manage')->with('error', 'Cannot delete: users or tasks are mapped to this product.');
        }
        $productModel->delete($id);
        return redirect()->to('/admin/products/manage')->with('success', 'Product deleted successfully.');
    }

    public function productMemberAdd(int $productId)
    {
        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/products/edit/' . $productId);
        }
        $productModel = new ProductModel();
        $product = $productModel->find($productId);
        if (!$product) {
            return redirect()->to('/admin/products/manage')->with('error', 'Product not found.');
        }
        $userId = (int) $this->request->getPost('user_id');
        $role = trim($this->request->getPost('role_in_product') ?: 'member');
        if (!$userId) {
            return redirect()->to('/admin/products/edit/' . $productId)->with('error', 'Select a user.');
        }
        if ($productModel->isMember($productId, $userId)) {
            return redirect()->to('/admin/products/edit/' . $productId)->with('error', 'User already has access.');
        }
        $productModel->addMember($productId, $userId, $role);
        return redirect()->to('/admin/products/edit/' . $productId)->with('success', 'Access granted successfully.');
    }

    public function productToggleDisabled(int $id)
    {
        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/products/manage');
        }
        $productModel = new ProductModel();
        $product = $productModel->find($id);
        if (!$product) {
            return redirect()->to('/admin/products/manage')->with('error', 'Product not found.');
        }
        $current = (int) ($product['is_disabled'] ?? 0);
        $new = $current ? 0 : 1;
        $productModel->skipValidation(true)->update($id, ['is_disabled' => $new]);
        $action = $new ? 'disabled' : 'enabled';
        return redirect()->to('/admin/products/manage')->with('success', "Product {$action} successfully.");
    }

    public function productMemberRemove(int $productId, int $userId)
    {
        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/products/edit/' . $productId);
        }
        $productModel = new ProductModel();
        $product = $productModel->find($productId);
        if (!$product) {
            return redirect()->to('/admin/products/manage')->with('error', 'Product not found.');
        }
        $productModel->removeMember($productId, $userId);
        return redirect()->to('/admin/products/edit/' . $productId)->with('success', 'Access revoked successfully.');
    }
}
