{extends file="layout/main.tpl"}
{block name="content"}
<h1>{$product.name|escape}</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<p>Start: {$product.start_date|escape} | End: {$product.end_date|escape}</p>
{if $product.github_repo_url}
<p>GitHub: <a href="{$product.github_repo_url|escape}" target="_blank">{$product.github_repo_url|escape}</a></p>
<form method="post" action="/products/sync/{$product.id}" style="margin-bottom:20px;">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <button type="submit" class="btn">Sync from GitHub Issues</button>
</form>
{/if}

<h2>Tasks</h2>
{if $can_manage_tasks}
<form method="post" action="/products/{$product.id}/tasks/add" style="margin-bottom: 20px; padding: 16px; background: #f8f8f8; border-radius: 6px;">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <strong>Add Task:</strong>
    <input type="text" name="title" placeholder="Task title" required style="width: 300px; padding: 8px; margin: 0 8px;">
    <select name="assignee_id" style="padding: 8px;">
        <option value="">— No assignee —</option>
        {foreach $all_users as $u}
        <option value="{$u.id}">{($u.first_name|default:'')|cat:' '|cat:($u.last_name|default:'')|escape} ({$u.email|escape})</option>
        {/foreach}
    </select>
    <button type="submit" class="btn">Add Task</button>
</form>
{/if}

{if empty($tasks)}
    <p>No tasks. Sync from GitHub or create manually.</p>
{else}
<table class="data-table" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px;">Title</th>
            <th style="padding: 10px;">Status</th>
            <th style="padding: 10px;">Assignee</th>
            {if $can_manage_tasks}<th style="padding: 10px;">Actions</th>{/if}
        </tr>
    </thead>
    <tbody>
        {foreach $tasks as $t}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$t.title|escape}</td>
            <td style="padding: 10px;">{$t.status|escape}</td>
            <td style="padding: 10px;">{$t.assignee_email|default:'—'|escape}</td>
            {if $can_manage_tasks}
            <td style="padding: 10px;">
                <a href="/products/{$product.id}/tasks/edit/{$t.id}" class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.85em;">Edit</a>
                <form method="post" action="/products/{$product.id}/tasks/delete/{$t.id}" style="display:inline;" onsubmit="return confirm('Delete this task?');">
                    <input type="hidden" name="{$csrf}" value="{$hash}">
                    <button type="submit" class="btn" style="padding: 4px 10px; font-size: 0.85em; background:#c00;">Delete</button>
                </form>
            </td>
            {/if}
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}

<p style="margin-top:20px;"><a href="/products" class="btn">Back to Products</a></p>
{/block}
