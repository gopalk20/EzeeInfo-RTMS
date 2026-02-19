{extends file="layout/main.tpl"}
{block name="content"}
<h1>Manage Products</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<div style="margin-bottom: 20px;">
    <a href="/admin/products/add" class="btn">+ Add Product</a>
    <a href="/admin/users" class="btn btn-secondary">Manage Users</a>
</div>

<table class="profile-table" style="margin-top: 20px;">
    <thead>
        <tr>
            <th>Name</th>
            <th>Product Lead</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {foreach $products as $p}
        <tr class="{if $p.is_disabled}row-disabled{/if}">
            <td>{$p.name|escape}</td>
            <td>{$p.lead_email|default:'—'|escape}</td>
            <td>
                {if $p.is_disabled}
                    <span style="color:#991b1b; font-weight:500;">Disabled</span>
                {else}
                    <span style="color:#059669;">Enabled</span>
                {/if}
            </td>
            <td>
                <a href="/admin/products/edit/{$p.id}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.9em;">Edit</a>
                <form method="post" action="/admin/products/{$p.id}/toggle-disabled" style="display:inline;">
                    <input type="hidden" name="{$csrf}" value="{$hash}">
                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.9em; background:#b45309;">{if $p.is_disabled}Enable{else}Disable{/if}</button>
                </form>
                <form method="post" action="/admin/products/delete/{$p.id}" style="display:inline;" onsubmit="return confirm('Delete this product? This cannot be undone.');">
                    <input type="hidden" name="{$csrf}" value="{$hash}">
                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.9em; background:#dc2626;">Remove</button>
                </form>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
<style>.row-disabled { opacity: 0.7; background: #fef2f2; }</style>
{/block}
