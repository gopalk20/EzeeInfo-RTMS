<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;
use App\Models\RoleModel;

/**
 * Test login credentials: php spark test:login
 */
class TestLogin extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'test:login';
    protected $description = 'Test if login credentials work';

    public function run(array $params)
    {
        $userModel = new UserModel();
        $user = $userModel->where('email', 'admin@example.com')->first();
        if (!$user) {
            CLI::error('User admin@example.com not found in database.');
            return 1;
        }
        $ok = password_verify('admin123', $user['password']);
        CLI::write('User found: ' . ($user['email'] ?? '') . ' (role_id: ' . ($user['role_id'] ?? 'null') . ')', 'yellow');
        CLI::write('Password admin123 verifies: ' . ($ok ? 'YES' : 'NO'), $ok ? 'green' : 'red');
        return $ok ? 0 : 1;
    }
}
