<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;
use App\Filters\AuthFilter;
use App\Filters\RoleFilter;

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array<string, class-string|list<class-string>> [filter_name => classname]
     *                                                     or [filter_name => [classname1, classname2, ...]]
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'auth'          => AuthFilter::class,
        'role'          => RoleFilter::class,
        'require_manager'               => \App\Filters\RequireManagerFilter::class,
        'require_product_lead_or_manager'=> \App\Filters\RequireProductLeadOrManagerFilter::class,
        'require_employee'              => \App\Filters\RequireEmployeeFilter::class,
        'require_finance_or_manager'     => \App\Filters\RequireFinanceOrManagerFilter::class,
        'require_super_admin'           => \App\Filters\RequireSuperAdminFilter::class,
        'login_rate_limit'              => \App\Filters\LoginRateLimitFilter::class,
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array<string, array<string, array<string, string>>>|array<string, list<string>>
     */
    public array $globals = [
        'before' => [
            'csrf',
        ],
        'after' => [
            'toolbar',
            'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don't expect could bypass the filter.
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     */
    public array $filters = [
        'auth' => ['before' => ['profile', 'profile/*', 'admin', 'admin/*', 'products', 'products/*', 'tasks', 'tasks/*', 'timesheet', 'timesheet/*', 'approval', 'approval/*', 'reports', 'reports/*']],
    ];
}
