{extends file="layout/main.tpl"}
{block name="content"}
<h1>{if $product}Edit Product{else}Add Product{/if}</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
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
