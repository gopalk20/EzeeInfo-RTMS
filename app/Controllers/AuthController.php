<?php

namespace App\Controllers;

use App\Libraries\SmartyEngine;
use App\Models\UserModel;
use App\Models\RoleModel;

class AuthController extends BaseController
{
    protected $helpers = ['form'];

    /**
     * Root / - Show login if not logged in, redirect to home if logged in.
     * Handles both GET (show form) and POST (process login).
     */
    public function index()
    {
        $session = session();
        if ($session->get('user_id')) {
            return redirect()->to('/home');
        }

        if (strtolower((string) $this->request->getMethod()) === 'post') {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required',
            ];
            if (!$this->validate($rules)) {
                return $this->loginForm($this->validator->getErrors(), $this->request->getPost('email'));
            }

            $email    = trim((string) $this->request->getPost('email'));
            $password = $this->request->getPost('password');

            try {
                $userModel = new UserModel();
                $user = $userModel->where('email', $email)->first();
            } catch (\Throwable $e) {
                log_message('error', 'Login DB error: ' . $e->getMessage());
                return $this->loginForm(['email' => 'System error. Please check logs.'], $email);
            }

            if (!$user) {
                log_message('info', 'Login failed: user not found for email ' . $email);
                return $this->loginForm(['email' => 'Invalid email or password.'], $email);
            }

            if (isset($user['is_active']) && (int) $user['is_active'] !== 1) {
                log_message('info', 'Login failed: user disabled for email ' . $email);
                return $this->loginForm(['email' => 'Your account has been disabled. Please contact an administrator.'], $email);
            }

            if (!password_verify($password, $user['password'] ?? '')) {
                log_message('info', 'Login failed: wrong password for email ' . $email);
                return $this->loginForm(['email' => 'Invalid email or password.'], $email);
            }

            $roleModel = new RoleModel();
            $role = $roleModel->find($user['role_id'] ?? 0);
            $roleName = $role['name'] ?? 'Employee';
            $userModel = new UserModel();
            $displayName = $userModel->getDisplayName($user);

            $session->set([
                'user_id'       => $user['id'],
                'user_email'    => $user['email'],
                'user_role'     => $roleName,
                'user'          => $user,
                'display_name'  => $displayName,
            ]);
            $redirect = redirect()->to('/home')->with('success', 'Welcome back!');
            $session->close();
            return $redirect;
        }

        return $this->loginForm();
    }

    protected function loginForm(array $errors = [], ?string $email = null): string
    {
        $smarty = new SmartyEngine();
        $data = [
            'title'   => 'EzeeInfo Resource Timesheet Management System',
            'success' => session()->getFlashdata('success'),
            'error'   => session()->getFlashdata('error'),
            'errors'  => $errors,
            'email'   => $email ?? '',
            'year'    => date('Y'),
            'csrf'    => csrf_token(),
            'hash'    => csrf_hash(),
        ];
        return $smarty->render('auth/login.tpl', $data);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/')->with('success', 'You have been logged out.');
    }

    public function profile()
    {
        $session = session();
        $user = $session->get('user');
        if (!$user) {
            return redirect()->to('/');
        }

        $userModel = new UserModel();
        $user = $userModel->find($user['id']) ?: $user;
        $role = $userModel->getRole($user);
        $team = $userModel->getTeam($user);
        $reportingManager = !empty($user['reporting_manager_id'])
            ? $userModel->find($user['reporting_manager_id'])
            : null;
        $reporting_manager_name = $reportingManager
            ? $userModel->getDisplayName($reportingManager)
            : '—';

        $smarty = new SmartyEngine();
        return $smarty->render('profile/view.tpl', [
            'title'         => 'My Profile',
            'user'          => $user,
            'role'          => $role,
            'team'          => $team,
            'role_name'     => $role['name'] ?? '—',
            'team_name'     => $team['name'] ?? '—',
            'reporting_manager_name' => $reporting_manager_name,
            'display_name'  => $userModel->getDisplayName($user),
            'user_email'    => $session->get('user_email'),
            'user_role'     => $session->get('user_role'),
            'is_super_admin'=> $session->get('user_role') === 'Super Admin',
            'success'       => session()->getFlashdata('success'),
        ]);
    }

    public function resetOwnPassword()
    {
        $session = session();
        $user = $session->get('user');
        if (!$user) {
            return redirect()->to('/');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'current_password' => 'required',
                'new_password'     => 'required|min_length[8]',
                'confirm_password'=> 'required|matches[new_password]',
            ];
            if (!$this->validate($rules)) {
                return $this->resetPasswordForm($this->validator->getErrors());
            }

            $userModel = new UserModel();
            $userRow = $userModel->find($user['id']);
            if (!password_verify($this->request->getPost('current_password'), $userRow['password'])) {
                return $this->resetPasswordForm(['current_password' => 'Current password is incorrect.']);
            }

            $userModel->skipValidation(true)->update($user['id'], [
                'password' => $this->request->getPost('new_password'),
            ]);
            $session->set('user', $userModel->find($user['id']));

            return redirect()->to('/profile')->with('success', 'Password updated successfully.');
        }

        return $this->resetPasswordForm();
    }

    protected function resetPasswordForm(array $errors = []): string
    {
        $session = session();
        $smarty = new SmartyEngine();
        return $smarty->render('profile/reset_password.tpl', [
            'title'         => 'Reset Password',
            'errors'        => $errors,
            'csrf'          => csrf_token(),
            'hash'          => csrf_hash(),
            'user_email'    => $session->get('user_email'),
            'user_role'     => $session->get('user_role'),
            'is_super_admin'=> $session->get('user_role') === 'Super Admin',
        ]);
    }

}
