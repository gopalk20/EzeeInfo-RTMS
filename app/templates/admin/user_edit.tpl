{extends file="layout/main.tpl"}
{block name="content"}
<h1>Edit User</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}
{if $errors}
    <div class="alert alert-error">
        <ul style="margin:0; padding-left:20px;">
            {foreach $errors as $err}
            <li>{$err|escape}</li>
            {/foreach}
        </ul>
    </div>
{/if}

<form method="post" action="/admin/users/edit/{$user.id}" style="max-width: 600px;">
    <input type="hidden" name="{$csrf}" value="{$hash}">

    <div class="form-group">
        <label for="username">Username / Employee ID <span style="color:#991b1b;">*</span></label>
        <input type="text" name="username" id="username" value="{$user.username|default:''|escape}" maxlength="64" required style="width:100%; padding: 8px 12px;">
    </div>
    <div class="form-group">
        <label for="first_name">First Name <span style="color:#991b1b;">*</span></label>
        <input type="text" name="first_name" id="first_name" value="{$user.first_name|default:''|escape}" maxlength="128" required style="width:100%; padding: 8px 12px;">
    </div>
    <div class="form-group">
        <label for="last_name">Last Name <span style="color:#991b1b;">*</span></label>
        <input type="text" name="last_name" id="last_name" value="{$user.last_name|default:''|escape}" maxlength="128" required style="width:100%; padding: 8px 12px;">
    </div>
    <div class="form-group">
        <label for="email">Email <span style="color:#991b1b;">*</span></label>
        <input type="email" name="email" id="email" value="{$user.email|default:''|escape}" maxlength="255" required style="width:100%; padding: 8px 12px;">
    </div>
    <div class="form-group">
        <label for="employee_id">Employee ID (alt)</label>
        <input type="text" name="employee_id" id="employee_id" value="{$user.employee_id|default:''|escape}" maxlength="64" placeholder="Optional" style="width:100%; padding: 8px 12px;">
    </div>
    <div class="form-group">
        <label for="phone">Phone</label>
        <input type="text" name="phone" id="phone" value="{$user.phone|default:''|escape}" maxlength="32" style="width:100%; padding: 8px 12px;">
    </div>
    <div class="form-group">
        <label for="role_id">Role <span style="color:#991b1b;">*</span></label>
        <select name="role_id" id="role_id" required style="width:100%; padding: 8px 12px;">
            {foreach $roles as $r}
            <option value="{$r.id}" {if isset($user.role_id) && $user.role_id == $r.id}selected{/if}>{$r.name|escape}</option>
            {/foreach}
        </select>
    </div>
    <div class="form-group">
        <label for="team_id">Team <span style="color:#991b1b;">*</span></label>
        <select name="team_id" id="team_id" required style="width:100%; padding: 8px 12px;">
            {foreach $teams as $t}
            <option value="{$t.id}" {if isset($user.team_id) && $user.team_id == $t.id}selected{/if}>{$t.name|escape}</option>
            {/foreach}
        </select>
    </div>

    {if $is_super_admin}
    <div class="form-group">
        <label for="monthly_cost">Monthly Cost (Salary)</label>
        <input type="number" name="monthly_cost" id="monthly_cost" value="{$user.monthly_cost|escape}" step="0.01" min="0" placeholder="0" style="width:100%; padding: 8px 12px;">
        <span style="color:#666; font-size:0.9em;">Used for per-day cost and billing rate.</span>
    </div>
    {/if}

    <div class="form-group">
        <label for="reporting_manager_id">Reporting Manager</label>
        <select name="reporting_manager_id" id="reporting_manager_id" style="width:100%; padding: 8px 12px;">
            <option value="">— None —</option>
            {foreach $managers as $m}
                {if $m.id != $user.id}
                <option value="{$m.id}" {if isset($user.reporting_manager_id) && $user.reporting_manager_id == $m.id}selected{/if}>
                    {($m.first_name|default:'')|cat:' '|cat:($m.last_name|default:'')|escape} ({$m.email|escape})
                </option>
                {/if}
            {/foreach}
        </select>
    </div>

    <div class="form-group">
        <label>Status</label>
        <div style="margin-top: 8px;">
            <label style="margin-right: 16px;"><input type="radio" name="is_active" value="1" {if ($user.is_active|default:1)}checked{/if}> Active</label>
            <label><input type="radio" name="is_active" value="0" {if !($user.is_active|default:1)}checked{/if}> Disabled</label>
        </div>
    </div>

    <div class="form-group">
        <label for="new_password">New Password <span style="color:#666; font-weight:normal;">(leave blank to keep current)</span></label>
        <input type="password" name="new_password" id="new_password" minlength="8" placeholder="Min 8 characters" style="width:100%; padding: 8px 12px;">
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" minlength="8" placeholder="Confirm new password" style="width:100%; padding: 8px 12px;">
    </div>

    <p style="margin-top: 24px;">
        <button type="submit" class="btn">Save Changes</button>
        <a href="/admin/users" class="btn btn-secondary">Cancel</a>
    </p>
</form>
{/block}
