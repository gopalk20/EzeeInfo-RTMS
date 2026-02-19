{extends file="layout/main.tpl"}
{block name="content"}
<div class="content-card">
<h1>My Timesheet</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<h2 style="margin-top: 24px;">My Time Entries (This Month)</h2>
{if empty($entries)}
    <p style="color: #666;">No time entries yet. Log time below to add your first entry.</p>
{else}
    <table class="data-table" style="width:100%; border-collapse: collapse; margin-top: 12px;">
        <thead>
            <tr style="background: #f0f0f0;">
                <th style="padding: 10px; text-align: left;">Date</th>
                <th style="padding: 10px; text-align: left;">Task / Product</th>
                <th style="padding: 10px; text-align: left;">Description</th>
                <th style="padding: 10px; text-align: left;">Hours</th>
                <th style="padding: 10px; text-align: left;">Rework</th>
                <th style="padding: 10px; text-align: left;">Status</th>
                <th style="padding: 10px; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            {foreach $entries as $e}
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;">{$e.work_date|escape}</td>
                <td style="padding: 10px;">{$e.task_title|default:'—'|escape} <span style="color:#666;">({$e.product_name|default:'—'|escape})</span></td>
                <td style="padding: 10px;">{$e.description|default:'—'|escape}</td>
                <td style="padding: 10px;">{$e.hours|escape}</td>
                <td style="padding: 10px;">{if $e.is_rework}Yes{else}—{/if}</td>
                <td style="padding: 10px;">{if ($e.status|default:'') == 'approved'}Approved{else}Pending Approval{/if}</td>
                <td style="padding: 10px;">{if ($e.status|default:'pending_approval') == 'pending_approval'}<a href="/timesheet/edit/{$e.id}" class="btn-link">Edit</a>{else}—{/if}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>
{/if}

<h2 style="margin-top: 32px;">Log Time</h2>
{if empty($tasks)}
    <p>No tasks assigned. You need at least one assigned task to log time.</p>
    <p><a href="/tasks" class="btn">View My Tasks</a></p>
{else}
<form method="post" action="{$log_action_url|escape}">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <div class="form-group">
        <label for="task_id">Task</label>
        <select name="task_id" id="task_id" required>
            <option value="">-- Select Task --</option>
            {foreach $tasks as $t}
            <option value="{$t.id}">{$t.title|escape} ({$t.product_name|escape})</option>
            {/foreach}
        </select>
    </div>
    <div class="form-group">
        <label for="work_date">Work Date</label>
        <input type="date" name="work_date" id="work_date" value="{if isset($request.work_date)}{$request.work_date|escape}{else}{$default_work_date|escape}{/if}" required>
    </div>
    <div class="form-group">
        <label for="hours">Hours</label>
        <input type="number" name="hours" id="hours" step="0.25" min="0.25" max="24" placeholder="e.g. 2.5" required>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" rows="2" placeholder="What did you work on?"></textarea>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_rework" value="1"> Mark as rework</label>
    </div>
    <p style="color: #666; font-size: 0.9em;">Daily limit: 24 hours max per day</p>
    <button type="submit" class="btn">Save Time Entry</button>
</form>
{/if}

<p style="margin-top: 24px;"><a href="/timesheet/view?period=daily{if $default_work_date}&amp;date={$default_work_date|escape}{/if}" class="btn">View Daily</a> <a href="/timesheet/view?period=weekly{if $default_work_date}&amp;date={$default_work_date|escape}{/if}" class="btn btn-secondary">View Weekly</a> <a href="/timesheet/view?period=monthly" class="btn btn-secondary">View Monthly</a></p>
</div>
{/block}
