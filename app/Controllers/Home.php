<?php

namespace App\Controllers;

use App\Libraries\SmartyEngine;
use App\Models\UserModel;

class Home extends BaseController
{
    /**
     * Display home page
     */
    public function index(): string
    {
        $smarty = new SmartyEngine();
        
        $data = [
            'title' => 'Resource Timesheet Management System',
            'message' => 'Efficiently manage and track resource timesheets with our comprehensive RTMS platform built on PHP 8.4, CodeIgniter 4, and modern web technologies.',
            'year' => date('Y'),
            'features' => [
                'Real-time Timesheet Tracking',
                'Resource Availability Management',
                'Project-based Time Allocation',
                'Automated Report Generation',
                'Role-based Access Control',
                'Multi-user Support',
            ],
        ];

        return $smarty->render('home.tpl', $data);
    }

    /**
     * Display users from database
     */
    public function users(): string
    {
        $smarty = new SmartyEngine();
        $userModel = new UserModel();
        
        try {
            $users = $userModel->findAll();
            
            $data = [
                'title' => 'Users List',
                'users' => $users,
                'total_users' => count($users),
            ];

            return $smarty->render('users.tpl', $data);
        } catch (\Exception $e) {
            $data = [
                'title' => 'Users List',
                'users' => [],
                'error' => 'Database not configured or table does not exist.',
            ];

            return $smarty->render('users.tpl', $data);
        }
    }

    /**
     * Show about page with version information
     */
    public function about(): string
    {
        $smarty = new SmartyEngine();
        
        $data = [
            'title' => 'About',
            'php_version' => phpversion(),
            'codeigniter_version' => '4.5.2',
            'smarty_version' => '5.5.1',
            'mysql_version' => 'MySQLi Driver',
        ];

        return $smarty->render('about.tpl', $data);
    }
}
