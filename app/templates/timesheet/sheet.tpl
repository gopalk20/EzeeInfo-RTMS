{extends file="layout/main.tpl"}
{block name="content"}
<div class="content-card sheet-page">
<h1 style="font-size: 1.25rem;">Time Sheet</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<div style="margin-bottom: 16px;">
    <a href="/timesheet/sheet?period=daily&date={$from|escape}" class="btn btn-sm {if $period != 'daily'}btn-secondary{/if}">Daily</a>
    <a href="/timesheet/sheet?period=weekly&date={$from|escape}" class="btn btn-sm {if $period != 'weekly'}btn-secondary{/if}">Weekly</a>
    <a href="/timesheet/sheet?period=monthly&month={$month_value|escape}" class="btn btn-sm {if $period != 'monthly'}btn-secondary{/if}">Monthly</a>
</div>

<form method="get" action="{$form_action|escape}" style="margin-bottom: 16px; padding: 12px; background: #f8f9fa; border-radius: 6px; font-size: 0.875rem;">
    <input type="hidden" name="period" value="{$period|escape}">
    {if $period == 'daily'}
    <label for="date">Date:</label>
    <input type="date" name="date" id="date" value="{$from|escape}" onchange="this.form.submit()" style="margin-left: 8px; padding: 6px 10px; font-size: 0.875rem;">
    {elseif $period == 'weekly'}
    <label for="date">Week of:</label>
    <input type="date" name="date" id="date" value="{$from|escape}" onchange="this.form.submit()" style="margin-left: 8px; padding: 6px 10px; font-size: 0.875rem;">
    {else}
    <label for="month">Month:</label>
    <input type="month" name="month" id="month" value="{$month_value|escape}" onchange="this.form.submit()" style="margin-left: 8px; padding: 6px 10px; font-size: 0.875rem;">
    {/if}
</form>

<p style="color:#666; font-size: 0.875rem;">{if $period == 'daily'}Date: {$from|escape}{else}{$period|capitalize}: {$from|escape} to {$to|escape}{/if}</p>
<p><a href="/timesheet" class="btn btn-sm">Log Time</a> <a href="/timesheet/view?period={$period|escape}{if $period=='daily'}&date={$from|escape}{elseif $period=='weekly'}&date={$from|escape}{else}&month={$month_value|escape}{/if}" class="btn btn-sm btn-secondary">View Summary</a></p>

<div style="overflow-x: auto; margin-top: 20px;">
<table class="timesheet-grid sheet-table">
    <thead>
        <tr>
            <th>PROJECTS</th>
            {foreach $week_days as $wd}
            <th>{$wd.label|escape}</th>
            {/foreach}
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        {if empty($entries)}
        <tr>
            <td colspan="{$grid_colspan|escape}" style="padding: 24px; text-align: center; color: #6b7280;">No time entries for this period. <a href="/timesheet">Log time</a> to add an entry.</td>
        </tr>
        {else}
        {foreach $rows as $row}
        <tr>
            <td>
                <div class="sheet-task-name">{$row.task_title|escape}</div>
                <div class="sheet-product-name">{$row.product_name|escape}</div>
                {if $row.max_hours}
                <div class="sheet-budget">{$row.used_hours|string_format:"%.0f"}/{$row.max_hours|string_format:"%.0f"} h</div>
                <div class="sheet-progress"><div class="sheet-progress-bar" style="width: {$row.pct_used|string_format:"%.0f"}%;"></div></div>
                {/if}
            </td>
            {foreach $row.day_hours as $h}
            <td>
                {if $h > 0}
                    {$h|string_format:"%.2f"}
                {else}
                    —
                {/if}
            </td>
            {/foreach}
            <td>
                {if $row.row_total > 0}{$row.row_total|string_format:"%.2f"}{else}—{/if}
            </td>
        </tr>
        {/foreach}
        {/if}
    </tbody>
    <tfoot>
        <tr>
            <td>Total</td>
            {foreach $daily_totals as $dt}
            <td>{$dt|string_format:"%.2f"}</td>
            {/foreach}
            <td>{$period_total|string_format:"%.2f"}</td>
        </tr>
    </tfoot>
</table>
</div>

{if empty($rows)}
<p class="sheet-msg">No tasks assigned. <a href="/tasks">View My Tasks</a> or ask your manager to assign tasks.</p>
{/if}

<h3 class="sheet-section-title">{if $period == 'daily'}Time Entries for This Day{elseif $period == 'weekly'}Time Entries for This Week{else}Time Entries for This Month{/if}</h3>
{if empty($entries)}
<p class="sheet-msg">No time entries. <a href="/timesheet">Log time</a> to add an entry.</p>
{else}
<div style="overflow-x: auto;">
<table class="sheet-entries-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Task / Product</th>
            <th>Description</th>
            <th>Hours</th>
            <th>Rework</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {foreach $entries as $e}
        <tr>
            <td>{$e.work_date|escape}</td>
            <td>{$e.task_title|default:'—'|escape} <span class="sheet-muted">({$e.product_name|default:'—'|escape})</span></td>
            <td>{$e.description|default:'—'|escape}</td>
            <td>{$e.hours|escape}</td>
            <td>{if $e.is_rework}Yes{else}—{/if}</td>
            <td>{if ($e.status|default:'') == 'approved'}Approved{else}Pending Approval{/if}</td>
            <td>{if ($e.status|default:'pending_approval') == 'pending_approval'}<a href="/timesheet/edit/{$e.id}" class="btn-link">Edit</a>{else}—{/if}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
</div>
{/if}

<p style="margin-top: 20px;"><a href="/products" class="btn btn-sm btn-secondary">+ Add Task</a> <a href="/timesheet/view?period=daily" class="btn btn-sm btn-secondary">Back to Timesheet</a></p>
</div>

<style>
.sheet-page { font-size: 0.875rem; }
.sheet-table, .sheet-entries-table { width: 100%; border-collapse: collapse; min-width: 600px; font-size: 0.875rem; }
.sheet-table th, .sheet-table td { padding: 8px 10px; border: 1px solid #e5e7eb; text-align: left; }
.sheet-table th:nth-child(n+2), .sheet-table td:nth-child(n+2) { text-align: center; }
.sheet-table td:last-child, .sheet-table th:last-child { text-align: right; }
.sheet-table thead tr { background: #374151; color: white; }
.sheet-table tbody tr:hover { background: #f9fafb; }
.sheet-table tfoot tr { background: #f3f4f6; font-weight: bold; }
.sheet-task-name { font-weight: 500; }
.sheet-product-name { font-size: 0.8rem; color: #6b7280; margin-top: 2px; }
.sheet-budget { font-size: 0.8rem; color: #059669; margin-top: 4px; }
.sheet-progress { width: 80px; height: 4px; background: #e5e7eb; border-radius: 2px; margin-top: 2px; }
.sheet-progress-bar { height: 100%; background: #059669; border-radius: 2px; }
.sheet-entries-table th, .sheet-entries-table td { padding: 8px 10px; border: 1px solid #e5e7eb; }
.sheet-entries-table thead tr { background: #f1f5f9; font-weight: 500; color: #374151; }
.sheet-entries-table tbody tr { border-bottom: 1px solid #eee; }
.sheet-muted { color: #6b7280; }
.sheet-msg { margin-top: 12px; color: #6b7280; font-size: 0.875rem; }
.sheet-section-title { margin-top: 24px; font-size: 1rem; font-weight: 600; }
</style>
{/block}
