{extends file="layout/main.tpl"}
{block name="content"}
<h1>{if $product}Edit Product{else}Add Product{/if}</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $product && (!$product.team_id || $product.team_id == '')}
    <div class="alert" style="background:#fef3c7; color:#92400e;">Set the Team below so team members can log time for this product on their timesheet.</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<form method="post" action="{if $product}/admin/products/edit/{$product.id}{else}/admin/products/add{/if}">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <div class="form-group">
        <label for="name">Product Name</label>
        <input type="text" name="name" id="name" value="{if $product}{$product.name|escape}{/if}" required maxlength="255">
    </div>
    <div class="form-group">
        <label for="github_repo_url">GitHub Repository URL</label>
        <input type="url" name="github_repo_url" id="github_repo_url" value="{if $product && isset($product.github_repo_url)}{$product.github_repo_url|escape}{/if}" placeholder="https://github.com/owner/repo" maxlength="512" style="width:100%; padding: 8px 12px;">
        <p style="color:#666; font-size:0.9em; margin-top:4px;">Paste the repository URL to sync Issues as tasks. Example: https://github.com/owner/repo</p>
    </div>
    <div class="form-group">
        <label for="product_lead_id">Product Lead</label>
        <select name="product_lead_id" id="product_lead_id">
            <option value="">— None —</option>
            {foreach $leads as $l}
            <option value="{$l.id}" {if $product && $product.product_lead_id == $l.id}selected{/if}>
                {($l.first_name|default:'')|cat:' '|cat:($l.last_name|default:'')|escape} ({$l.email|escape})
            </option>
            {/foreach}
        </select>
    </div>
    <div class="form-group">
        <label for="team_id">Team (for billing)</label>
        <select name="team_id" id="team_id">
            <option value="">— Not mapped —</option>
            {foreach $teams as $t}
            <option value="{$t.id}" {if $product && isset($product.team_id) && $product.team_id == $t.id}selected{/if}>{$t.name|escape}</option>
            {/foreach}
        </select>
        <p style="color:#666; font-size:0.9em; margin-top:4px;">Only team members can bill timesheet for this product. <strong>Products without a team will not appear on the timesheet.</strong> Leave products are exempt.</p>
    </div>
    <p><button type="submit" class="btn">Save</button> <a href="/admin/products/manage" class="btn btn-secondary">Cancel</a></p>
</form>
{if $product}
<form method="post" action="/admin/products/{$product.id}/toggle-disabled" style="display:inline; margin-top: 12px;" onsubmit="return confirm('{if $product.is_disabled}Enable{else}Disable{/if} this product?');">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <button type="submit" class="btn" style="background: {if $product.is_disabled}#059669{else}#b45309{/if};">{if $product.is_disabled}Enable Product{else}Disable Product{/if}</button>
</form>
{/if}

{if $product}
<h2 style="margin-top: 28px;">Grant/Revoke Access (Manager & Product Members)</h2>
<p style="color:#666; margin-bottom: 12px;">Add or remove user access to this product.</p>

<form method="post" action="/admin/products/{$product.id}/members/add" style="margin-bottom: 20px; padding: 16px; background: #f8f8f8; border-radius: 6px;">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <strong>Grant access:</strong>
    <select name="user_id" required style="padding: 8px; margin: 0 8px;">
        <option value="">— Select user —</option>
        {foreach $all_users as $u}
        <option value="{$u.id}">{($u.first_name|default:'')|cat:' '|cat:($u.last_name|default:'')|escape} ({$u.email|escape})</option>
        {/foreach}
    </select>
    <select name="role_in_product" style="padding: 8px;">
        <option value="manager">Manager</option>
        <option value="member">Member</option>
    </select>
    <button type="submit" class="btn">Grant Access</button>
</form>

<table class="profile-table" style="margin-top: 12px;">
    <thead>
        <tr>
            <th>User</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {foreach $members as $m}
        <tr>
            <td>{($m.first_name|default:'')|cat:' '|cat:($m.last_name|default:'')|escape} ({$m.email|escape})</td>
            <td>{$m.role_in_product|escape}</td>
            <td>
                <form method="post" action="/admin/products/{$product.id}/members/remove/{$m.user_id}" style="display:inline;" onsubmit="return confirm('Revoke access?');">
                    <input type="hidden" name="{$csrf}" value="{$hash}">
                    <button type="submit" class="btn btn-sm" style="background:#c00;">Revoke</button>
                </form>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{if empty($members)}
<p style="color:#666;">No additional members. Product Lead is set above. Use Grant Access to add managers/members.</p>
{/if}
{/if}
{/block}
