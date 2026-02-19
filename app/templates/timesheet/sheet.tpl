{extends file="layout/main.tpl"}
{block name="content"}
<div class="content-card">
<h1>Time Sheet</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<form method="get" action="/timesheet/sheet" style="margin-bottom: 20px; padding: 16px; background: #f8f9fa; border-radius: 8px;">
    <label for="date" style="font-weight:500;">Week of:</label>
    <input type="date" name="date" id="date" value="{$from|escape}" onchange="this.form.submit()" style="margin-left: 8px; padding: 8px 12px; border-radius: 6px;">
</form>

<p style="color:#666;">Week: {$from|escape} to {$to|escape}</p>
<p><a href="/timesheet" class="btn">Log Time</a> <a href="/timesheet/view?period=weekly&date={$from|escape}" class="btn btn-secondary">View Summary</a></p>

<div style="overflow-x: auto; margin-top: 24px;">
<table class="timesheet-grid" style="width:100%; border-collapse: collapse; min-width: 720px;">
    <thead>
        <tr style="background: #374151; color: white;">
            <th style="padding: 12px; text-align: left; min-width: 200px;">PROJECTS</th>
            {foreach $week_days as $wd}
            <th style="padding: 12px; text-align: center; min-width: 80px;">{$wd.label|escape}</th>
            {/foreach}
            <th style="padding: 12px; text-align: right; font-weight: bold;">Total</th>
        </tr>
    </thead>
    <tbody>
        {foreach $rows as $row}
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 12px; vertical-align: top;">
                <div style="font-weight: 500;">{$row.task_title|escape}</div>
                <div style="font-size: 0.85em; color: #6b7280;">{$row.product_name|escape}</div>
                {if $row.max_hours}
                <div style="font-size: 0.8em; margin-top: 4px; color: #059669;">
                    {$row.used_hours|string_format:"%.0f"}/{$row.max_hours|string_format:"%.0f"} h
                </div>
                <div style="width: 100px; height: 4px; background: #e5e7eb; border-radius: 2px; margin-top: 2px;">
                    <div style="height: 100%; width: {$row.pct_used|string_format:"%.0f"}%; background: #059669; border-radius: 2px;"></div>
                </div>
                {/if}
            </td>
            {foreach $row.day_hours as $h}
            <td style="padding: 12px; text-align: center;">
                {if $h > 0}
                    {$h|string_format:"%.2f"}
                {else}
                    —
                {/if}
            </td>
            {/foreach}
            <td style="padding: 12px; text-align: right; font-weight: 500;">
                {if $row.row_total > 0}{$row.row_total|string_format:"%.2f"}{else}—{/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
    <tfoot>
        <tr style="background: #f3f4f6; font-weight: bold;">
            <td style="padding: 12px;">Total</td>
            {foreach $daily_totals as $dt}
            <td style="padding: 12px; text-align: center;">{$dt|string_format:"%.2f"}</td>
            {/foreach}
            <td style="padding: 12px; text-align: right;">{$week_total|string_format:"%.2f"}</td>
        </tr>
    </tfoot>
</table>
</div>

{if empty($rows)}
<p style="margin-top: 20px; color: #6b7280;">No tasks assigned. <a href="/tasks">View My Tasks</a> or ask your manager to assign tasks.</p>
{/if}

<p style="margin-top: 24px;"><a href="/products" class="btn btn-secondary">+ Add Task</a> <a href="/timesheet" class="btn btn-secondary">Back to My Timesheet</a></p>
</div>

<style>
.timesheet-grid td, .timesheet-grid th { border: 1px solid #e5e7eb; }
.timesheet-grid tbody tr:hover { background: #f9fafb; }
</style>
{/block}
