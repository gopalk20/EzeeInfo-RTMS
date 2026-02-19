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
.admin-table tbody tr.row-disabled { opacity: 0.7; background: #fef2f2; }
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
<h1>Manage Products</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
    <a href="/admin/products/add" class="btn">+ Add Product</a>
    <a href="/admin/users" class="btn btn-secondary">Manage Users</a>
    <form method="get" action="/admin/products/manage" class="admin-search-row">
        <label for="q">Search:</label>
        <input type="text" name="q" id="q" value="{$search|escape}" placeholder="Product name">
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
    </form>
</div>

<div class="admin-table-wrap">
<table class="admin-table">
    <thead>
        <tr>
            <th><a href="?q={$search|escape:url}&sort=id&dir={if $sort=='id' && $dir=='asc'}desc{else}asc{/if}"># ↕</a></th>
            <th><a href="?q={$search|escape:url}&sort=name&dir={if $sort=='name' && $dir=='asc'}desc{else}asc{/if}">Name ↕</a></th>
            <th>Account Manager</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th><a href="?q={$search|escape:url}&sort=created&dir={if $sort=='created' && $dir=='asc'}desc{else}asc{/if}">Created At ↕</a></th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {foreach $products as $p}
        <tr class="{if $p.is_disabled}row-disabled{/if}">
            <td><span class="admin-id">{$p.id|escape}</span></td>
            <td><a href="/products/view/{$p.id}" style="color:inherit; text-decoration:none;">{$p.name|escape}</a></td>
            <td>{$p.lead_name|escape}</td>
            <td>{$p.start_date|default:'—'|escape}</td>
            <td>{$p.end_date|default:'—'|escape}</td>
            <td>{$p.created_at_fmt|escape}</td>
            <td>{if $p.is_disabled}<span class="admin-badge admin-badge-inactive">Disabled</span>{else}<span class="admin-badge admin-badge-active">Active</span>{/if}</td>
            <td>
                <div class="admin-actions">
                    <a href="/products/view/{$p.id}" title="View">🔍</a>
                    <a href="/admin/products/edit/{$p.id}" title="Edit">✎</a>
                    <form method="post" action="/admin/products/delete/{$p.id}" style="display:inline;" onsubmit="return confirm('Delete this product? This cannot be undone.');">
                        <input type="hidden" name="{$csrf}" value="{$hash}">
                        <button type="submit" title="Delete">✕</button>
                    </form>
                </div>
            </td>
        </tr>
        {foreachelse}
        <tr><td colspan="8" style="padding: 24px; color: #64748b;">No products found.</td></tr>
        {/foreach}
    </tbody>
</table>
</div>
{/block}
