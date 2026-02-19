{extends file="layout/main.tpl"}
{block name="content"}
<h1>Edit User</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<p style="color:#666; margin-bottom:20px;">Edit reporting manager, status, or reset password for <strong>{($user.first_name|default:'')|cat:' '|cat:($user.last_name|default:'')|escape}</strong> ({$user.email|escape})</p>

<form method="post" action="/admin/users/edit/{$user.id}" style="max-width: 500px;">
    <input type="hidden" name="{$csrf}" value="{$hash}">

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
