<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title|default:'EzeeInfo Timesheet Entry'|escape}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #e8ecf1; min-height: 100vh; }
        .app-wrapper { display: flex; min-height: 100vh; }
        .header {
            position: fixed; top: 0; left: 0; right: 0; height: 56px;
            background: #4a3f6e;
            display: flex; align-items: center; padding: 0 20px; z-index: 100;
        }
        .header-brand { color: white; font-weight: 700; font-size: 1.25rem; margin-right: 16px; }
        .header-brand .accent { color: #a78bfa; }
        .header-toggle {
            background: none; border: none; color: white; cursor: pointer;
            padding: 8px; font-size: 1.2rem;
        }
        .header-right { margin-left: auto; display: flex; align-items: center; gap: 16px; }
        .header-bell {
            position: relative; color: white; text-decoration: none; padding: 8px;
        }
        .header-bell .badge {
            position: absolute; top: 2px; right: 2px;
            background: #10b981; color: white; font-size: 0.7rem;
            min-width: 18px; height: 18px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
        }
        .header-user {
            display: flex; align-items: center; gap: 8px; cursor: pointer;
            color: white; padding: 6px 10px; border-radius: 6px;
        }
        .header-user:hover { background: rgba(255,255,255,0.1); }
        .header-user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: #6f42c1; color: white;
            display: flex; align-items: center; justify-content: center; font-weight: 600;
        }
        .sidebar {
            position: fixed; top: 56px; left: 0; bottom: 0; width: 240px;
            background: #fff; border-right: 1px solid #e2e8f0; overflow-y: auto; z-index: 99;
        }
        .sidebar-nav { padding: 20px 0; }
        .sidebar-title { font-size: 0.7rem; color: #94a3b8; padding: 0 20px 12px; text-transform: uppercase; letter-spacing: 0.05em; }
        .sidebar-nav a {
            display: flex; align-items: center; padding: 12px 20px; color: #475569;
            text-decoration: none; font-size: 0.95rem; border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover { background: #f1f5f9; color: #4a3f6e; }
        .sidebar-nav a.active { background: #f5f3ff; color: #6f42c1; border-left-color: #6f42c1; font-weight: 500; }
        .main-content {
            flex: 1; margin-left: 240px; margin-top: 56px; min-height: calc(100vh - 56px);
            padding: 24px; background: #e8ecf1;
        }
        .page-header { margin-bottom: 24px; }
        .page-title { font-size: 1.75rem; color: #1e293b; font-weight: 600; }
        .page-subtitle { font-size: 0.9rem; color: #64748b; margin-top: 4px; }
        .content-card {
            background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            padding: 24px; margin-bottom: 24px;
        }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 16px; }
        .alert-success { background: #ecfdf5; color: #065f46; }
        .alert-error { background: #fef2f2; color: #991b1b; }
        .profile-table { width: 100%; border-collapse: collapse; }
        .profile-table th, .profile-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .profile-table th { width: 180px; color: #64748b; font-weight: 500; }
        .btn {
            display: inline-block; padding: 8px 16px; background: #6f42c1; color: white;
            text-decoration: none; border-radius: 6px; border: none; cursor: pointer; font-size: 0.9rem;
        }
        .btn:hover { background: #5a32a3; }
        .btn-sm {
            padding: 4px 10px; font-size: 0.85em;
        }
        .btn-icon {
            display: inline-flex; align-items: center; justify-content: center; padding: 6px; min-width: 32px; min-height: 32px;
            font-size: 1rem; line-height: 1;
        }
        .btn-icon img, .btn-icon svg { width: 18px; height: 18px; }
        .btn-secondary { background: #64748b; }
        .btn-secondary:hover { background: #475569; }
        .btn-link { background: transparent; color: #6f42c1; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; }
        .dropdown { position: relative; }
        .dropdown-menu {
            display: none; position: absolute; top: 100%; right: 0; margin-top: 4px;
            background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 180px; z-index: 1000;
        }
        .dropdown.open .dropdown-menu { display: block; }
        .dropdown-header { padding: 16px; background: #4a3f6e; color: white; border-radius: 8px 8px 0 0; display: flex; align-items: center; gap: 12px; }
        .dropdown-item { display: block; padding: 12px 16px; color: #475569; text-decoration: none; }
        .dropdown-item:hover { background: #f8fafc; }
        .footer { text-align: right; margin-top: 32px; color: #94a3b8; font-size: 0.85rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .data-table th { background: #f8fafc; font-weight: 500; color: #475569; }
    </style>
</head>
<body>
    <header class="header">
        <button type="button" class="header-toggle" id="sidebarToggle" aria-label="Toggle menu">☰</button>
        <a href="/home" class="header-brand"><span class="accent">Ezee</span>Info - Timesheet Entry</a>
        <div class="header-right">
            <a href="/approval" class="header-bell" title="Pending approvals">
                <span>🔔</span>
                {if isset($pending_count) && $pending_count > 0}
                <span class="badge">{$pending_count}</span>
                {else}
                <span class="badge">0</span>
                {/if}
            </a>
            <div class="dropdown" id="userDropdown">
                <div class="header-user">
                    <div class="header-user-avatar">{if isset($user_initial)}{$user_initial|escape}{else}U{/if}</div>
                    <span>{if isset($display_name)}{$display_name|escape}{elseif isset($user_email)}{$user_email|escape}{else}User{/if}</span>
                </div>
                <div class="dropdown-menu">
                    <div class="dropdown-header">
                        <div class="header-user-avatar">{if isset($user_initial)}{$user_initial|escape}{else}U{/if}</div>
                        <span>{if isset($display_name)}{$display_name|escape}{elseif isset($user_email)}{$user_email|escape}{else}User{/if}</span>
                    </div>
                    <a href="/profile" class="dropdown-item">Profile</a>
                    <a href="/logout" class="dropdown-item">Log out</a>
                </div>
            </div>
        </div>
    </header>
    <aside class="sidebar" id="sidebar">
        <nav class="sidebar-nav">
            <div class="sidebar-title">Main Navigation</div>
            <a href="/home" class="{if isset($nav_active) && $nav_active=='home'}active{/if}">🏠 Dashboard</a>
            <a href="/timesheet/view?period=daily" class="{if isset($nav_active) && in_array($nav_active, ['timesheet', 'view'])}active{/if}">⏱ Timesheet</a>
            <a href="/tasks" class="{if isset($nav_active) && $nav_active=='tasks'}active{/if}">📋 My Tasks</a>
            <a href="/products" class="{if isset($nav_active) && $nav_active=='products'}active{/if}">📦 Products</a>
            <a href="/milestones" class="{if isset($nav_active) && $nav_active=='milestones'}active{/if}">🎯 Milestones</a>
            {if isset($user_role) && in_array($user_role, ['Manager', 'Product Lead', 'Super Admin'])}
            <a href="/approval" class="{if isset($nav_active) && $nav_active=='approval'}active{/if}">✅ Approval</a>
            <a href="/timesheet/team" class="{if isset($nav_active) && $nav_active=='team'}active{/if}">👥 Team Timesheet</a>
            {/if}
            {if isset($user_role) && ($user_role == 'Finance' || $user_role == 'Manager' || $user_role == 'Super Admin')}
            <a href="/reports" class="{if isset($nav_active) && $nav_active=='reports'}active{/if}">📊 Report</a>
            {/if}
            {if isset($user_role) && in_array($user_role, ['Manager', 'Super Admin'])}
            <a href="/costing" class="{if isset($nav_active) && $nav_active=='costing'}active{/if}">💰 Costing</a>
            <a href="/admin/dashboard" class="{if isset($nav_active) && $nav_active=='admin_dashboard'}active{/if}">📊 Admin Dashboard</a>
            {/if}
            {if isset($is_super_admin) && $is_super_admin}
            <div class="sidebar-title" style="margin-top: 16px;">Admin</div>
            <a href="/admin/users" class="{if isset($nav_active) && $nav_active=='users'}active{/if}">👤 Manage Users</a>
            <a href="/admin/products/manage" class="{if isset($nav_active) && $nav_active=='products_manage'}active{/if}">📦 Manage Products</a>
            {/if}
        </nav>
    </aside>
    <main class="main-content">
        {block name="content"}{/block}
        <div class="footer">Copyright © {$year|default:'2026'|escape} Ezee Info Solutions. All rights reserved.</div>
    </main>
    <script>
        document.getElementById('userDropdown')?.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('open');
        });
        document.addEventListener('click', function() {
            document.getElementById('userDropdown')?.classList.remove('open');
        });
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });
    </script>
</body>
</html>
