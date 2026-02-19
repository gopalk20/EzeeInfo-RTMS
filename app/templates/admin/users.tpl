{extends file="layout/main.tpl"}
{block name="content"}
<style>
.admin-table-wrap { margin-top: 20px; }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table thead tr { background: #f1f5f9; }
.admin-table th { padding: 12px 10px; text-align: left; font-weight: 500; color: #374151; }
.admin-table th a { color: inherit; text-decoration: none; }
.admin-table th a:hover { text-decoration: underline; }
.admin-table td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
.admin-table tbody tr:hover { background: #f9fafb; }
.admin-id { color: #0284c7; font-weight: 500; }
.admin-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.85em; font-weight: 500; }
.admin-badge-active { background: #dcfce7; color: #166534; }
.admin-badge-inactive { background: #fee2e2; color: #991b1b; }
.admin-actions { display: flex; gap: 8px; align-items: center; }
.admin-actions a, .admin-actions button { padding: 6px 8px; min-width: 32px; min-height: 32px; display: inline-flex; align-items: center; justify-content: center; border: none; background: transparent; cursor: pointer; color: #64748b; text-decoration: none; border-radius: 4px; font-size: 1rem; }
.admin-actions a:hover, .admin-actions button:hover { color: #6f42c1; background: #f3f4f6; }
.admin-search-row { display: flex; justify-content: flex-end; align-items: center; margin-bottom: 16px; gap: 12px; }
.admin-search-row input { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; width: 220px; }
</style>
<h1>Manage Users</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; align-items: center; gap: 16px;">
    <a href="/admin/users/add" class="btn">Add New User</a>
    <a href="/admin/products/manage" class="btn btn-secondary">Manage Products</a>
</div>

<div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px;">
    <form method="get" action="/admin/users" id="team-filter-form">
        <input type="hidden" name="sort" value="{$sort|escape}">
        <input type="hidden" name="dir" value="{$dir|escape}">
        <input type="hidden" name="q" value="{$search|escape}">
        <label for="team" style="font-weight:500; margin-right:8px;">Department/Team:</label>
        <select name="team" id="team" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; margin-right: 16px;">
            <option value="">— All —</option>
            {foreach $teams as $t}
            <option value="{$t.name|escape}" {if $filter_team == $t.name}selected{/if}>{$t.name|escape}</option>
            {/foreach}
        </select>
    </form>
    <form method="get" action="/admin/users" class="admin-search-row">
        {if $filter_team}<input type="hidden" name="team" value="{$filter_team|escape}">{/if}
        <label for="q">Search:</label>
        <input type="text" name="q" id="q" value="{$search|escape}" placeholder="Name, email, employee ID">
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
    </form>
</div>

<div class="admin-table-wrap">
<table class="admin-table">
    <thead>
        <tr>
            <th><a href="?team={$filter_team|escape:url}&q={$search|escape:url}&sort=id&dir={if $sort=='id' && $dir=='asc'}desc{else}asc{/if}"># ↕</a></th>
            <th><a href="?team={$filter_team|escape:url}&q={$search|escape:url}&sort=name&dir={if $sort=='name' && $dir=='asc'}desc{else}asc{/if}">Name ↕</a></th>
            <th>Employee ID</th>
            <th><a href="?team={$filter_team|escape:url}&q={$search|escape:url}&sort=email&dir={if $sort=='email' && $dir=='asc'}desc{else}asc{/if}">Email ↕</a></th>
            <th>Team</th>
            <th>Role</th>
            <th>Reporting Manager</th>
            <th><a href="?team={$filter_team|escape:url}&q={$search|escape:url}&sort=created&dir={if $sort=='created' && $dir=='asc'}desc{else}asc{/if}">Created At ↕</a></th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {foreach $users as $u}
        <tr>
            <td><span class="admin-id">{$u.id|escape}</span></td>
            <td>{$u.display_name|escape}</td>
            <td>{$u.username|default:'—'|escape}</td>
            <td>{$u.email|escape}</td>
            <td>{$u.team_name|escape}</td>
            <td>{$u.role_name|escape}</td>
            <td>{$u.reporting_manager_name|escape}</td>
            <td>{$u.created_at_fmt|escape}</td>
            <td>{if ($u.is_active|default:1)}<span class="admin-badge admin-badge-active">Active</span>{else}<span class="admin-badge admin-badge-inactive">Disabled</span>{/if}</td>
            <td>
                <div class="admin-actions">
                    <a href="/admin/users/edit/{$u.id}" title="View/Edit">🔍</a>
                    <a href="/admin/users/edit/{$u.id}" title="Edit">✎</a>
                    <form method="post" action="/admin/users/{$u.id}/toggle-active" style="display:inline;" onsubmit="return confirm('{if ($u.is_active|default:1)}Disable{else}Enable{/if} this user?');">
                        <input type="hidden" name="{$csrf}" value="{$hash}">
                        <button type="submit" title="{if ($u.is_active|default:1)}Disable{else}Enable{/if}">✕</button>
                    </form>
                </div>
            </td>
        </tr>
        {foreachelse}
        <tr><td colspan="10" style="padding: 24px; color: #64748b;">No users found.</td></tr>
        {/foreach}
    </tbody>
</table>
</div>
{/block}
