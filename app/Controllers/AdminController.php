<?php

namespace App\Controllers;

use App\Libraries\SmartyEngine;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\TeamModel;
use App\Models\ProductModel;

class AdminController extends BaseController
{
    protected $helpers = ['form'];

    public function users()
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();
        $teamModel = new TeamModel();

        $teamFilter = $this->request->getGet('team');
        $builder = $userModel->select('users.*, rm.first_name as rm_first, rm.last_name as rm_last')
            ->join('users as rm', 'rm.id = users.reporting_manager_id', 'left');
        if ($teamFilter !== null && $teamFilter !== '') {
            $builder->join('teams', 'teams.id = users.team_id', 'inner')
                ->where('teams.name', $teamFilter);
        }
        $users = $builder->findAll();

        foreach ($users as &$u) {
            $u['role_name'] = ($userModel->getRole($u)['name'] ?? '—');
            $u['team_name'] = ($userModel->getTeam($u)['name'] ?? '—');
            $u['reporting_manager_name'] = trim(($u['rm_first'] ?? '') . ' ' . ($u['rm_last'] ?? '')) ?: '—';
        }
        $teams = $teamModel->orderBy('name')->findAll();

        $smarty = new SmartyEngine();
        return $smarty->render('admin/users.tpl', [
            'title'         => 'Manage Users',
            'nav_active'    => 'users',
            'users'         => $users,
            'teams'         => $teams,
            'filter_team'   => $teamFilter ?? '',
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

        if ($this->request->getMethod() === 'post') {
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

        if ($this->request->getMethod() === 'post') {
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

            $msg = 'User updated successfully.';
            if (!empty($updates['password'])) {
                $msg = 'User updated. Password reset successfully.';
            }
            return redirect()->to('/admin/users')->with('success', $msg);
        }

        $roleModel = new RoleModel();
        $roleIds = $roleModel->whereIn('name', ['Manager', 'Product Lead', 'Super Admin'])->findColumn('id');
        $managers = $roleIds ? $userModel->whereIn('role_id', $roleIds)->findAll() : [];

        $user['role_name'] = ($userModel->getRole($user)['name'] ?? '—');
        $user['team_name'] = ($userModel->getTeam($user)['name'] ?? '—');

        $smarty = new SmartyEngine();
        return $smarty->render('admin/user_edit.tpl', [
            'title'         => 'Edit User',
            'user'          => $user,
            'managers'      => $managers,
            'user_email'    => session()->get('user_email'),
            'success'       => session()->getFlashdata('success'),
            'error'         => session()->getFlashdata('error'),
            'csrf'          => csrf_token(),
            'hash'          => csrf_hash(),
        ]);
    }

    public function setReportingManager(int $id)
    {
        if ($this->request->getMethod() !== 'post') {
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
        if ($this->request->getMethod() !== 'post') {
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
        if ($this->request->getMethod() === 'post') {
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
        $products = $productModel->select('products.*, u.email as lead_email')
            ->join('users u', 'u.id = products.product_lead_id', 'left')
            ->findAll();

        $smarty = new SmartyEngine();
        return $smarty->render('admin/products_manage.tpl', [
            'title'          => 'Manage Products',
            'nav_active'     => 'products_manage',
            'products'       => $products,
            'user_email'     => session()->get('user_email'),
            'success'        => session()->getFlashdata('success'),
            'error'          => session()->getFlashdata('error'),
            'csrf'           => csrf_token(),
            'hash'           => csrf_hash(),
        ]);
    }

    public function productAdd()
    {
        if ($this->request->getMethod() === 'post') {
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
        if ($this->request->getMethod() === 'post') {
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
        if ($this->request->getMethod() !== 'post') {
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
        if ($this->request->getMethod() !== 'post') {
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
        if ($this->request->getMethod() !== 'post') {
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
        if ($this->request->getMethod() !== 'post') {
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
