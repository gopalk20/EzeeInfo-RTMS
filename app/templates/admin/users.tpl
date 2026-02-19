{extends file="layout/main.tpl"}
{block name="content"}
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

<form method="get" action="/admin/users" style="margin-bottom: 20px; padding: 16px; background: #f8f9fa; border-radius: 8px;">
    <label for="team" style="font-weight:500; margin-right:8px;">Filter by Team:</label>
    <select name="team" id="team" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
        <option value="">— All Teams —</option>
        {foreach $teams as $t}
        <option value="{$t.name|escape}" {if $filter_team == $t.name}selected{/if}>{$t.name|escape}</option>
        {/foreach}
    </select>
</form>

<table class="data-table" style="width:100%; border-collapse: collapse; margin-top: 20px;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px; text-align: left;">Name</th>
            <th style="padding: 10px; text-align: left;">Email</th>
            <th style="padding: 10px; text-align: left;">Role</th>
            <th style="padding: 10px; text-align: left;">Team</th>
            <th style="padding: 10px; text-align: left;">Reporting Manager</th>
            <th style="padding: 10px; text-align: left;">Status</th>
            <th style="padding: 10px; text-align: left;">Actions</th>
        </tr>
    </thead>
    <tbody>
        {foreach $users as $u}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{($u.first_name|default:'')|cat:' '|cat:($u.last_name|default:'')|escape}</td>
            <td style="padding: 10px;">{$u.email|escape}</td>
            <td style="padding: 10px;">{$u.role_name|escape}</td>
            <td style="padding: 10px;">{$u.team_name|escape}</td>
            <td style="padding: 10px;">{$u.reporting_manager_name|escape}</td>
            <td style="padding: 10px;">{if ($u.is_active|default:1)}<span style="color:#059669;">Active</span>{else}<span style="color:#dc2626;">Disabled</span>{/if}</td>
            <td style="padding: 10px;"><a href="/admin/users/edit/{$u.id}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.9em;">Edit</a></td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/block}
