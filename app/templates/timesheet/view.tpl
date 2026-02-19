{extends file="layout/main.tpl"}
{block name="content"}
<div class="content-card">
<h1>Timesheet Summary</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<div style="margin-bottom: 20px;">
    <a href="/timesheet/sheet" class="btn">Time Sheet</a>
    <a href="/timesheet/view?period=daily" class="btn {if $period == 'daily'}btn-secondary{/if}">Daily</a>
    <a href="/timesheet/view?period=weekly" class="btn {if $period == 'weekly'}btn-secondary{/if}">Weekly</a>
    <a href="/timesheet/view?period=monthly" class="btn {if $period == 'monthly'}btn-secondary{/if}">Monthly</a>
</div>

<form method="get" action="/timesheet/view" style="margin-bottom: 20px; padding: 16px; background: #f8f8f8; border-radius: 6px;">
    <input type="hidden" name="period" value="{$period|escape}">
    {if $period == 'daily'}
    <label for="date">Select date:</label>
    <input type="date" name="date" id="date" value="{$from|escape}" onchange="this.form.submit()" style="margin-left: 8px;">
    {elseif $period == 'weekly'}
    <label for="date">Select week (any day):</label>
    <input type="date" name="date" id="date" value="{$from|escape}" onchange="this.form.submit()" style="margin-left: 8px;">
    {else}
    <label for="month">Select month:</label>
    <input type="month" name="month" id="month" value="{$month_value|escape}" onchange="this.form.submit()" style="margin-left: 8px;">
    {/if}
</form>

<p style="color:#666;">Period: {$from|escape} to {$to|escape}</p>

<h3 style="margin-top: 24px;">Time Entry Details</h3>
{if empty($entries)}
<p style="margin-top: 12px; color: #666;">No time entries for this period. <a href="/timesheet">Log time</a> or select a different date above.</p>
{else}
<table class="data-table" style="width:100%; border-collapse: collapse; margin-top: 12px;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px; text-align: left;">Date</th>
            <th style="padding: 10px; text-align: left;">Project</th>
            <th style="padding: 10px; text-align: left;">Task</th>
            <th style="padding: 10px; text-align: right;">Hours</th>
        </tr>
    </thead>
    <tbody>
        {foreach $entries as $e}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$e.work_date|escape}</td>
            <td style="padding: 10px;">{$e.product_name|escape}</td>
            <td style="padding: 10px;">{$e.task_title|escape}</td>
            <td style="padding: 10px; text-align: right;">{$e.hours|escape}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}

{if !empty($grouped)}
<h3 style="margin-top: 28px;">Summary by Project</h3>
<table class="data-table" style="width:100%; border-collapse: collapse; margin-top: 12px;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px; text-align: left;">Project Name</th>
            <th style="padding: 10px; text-align: right;">Hours</th>
        </tr>
    </thead>
    <tbody>
        {foreach $grouped as $row}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$row.project_name|escape}</td>
            <td style="padding: 10px; text-align: right;">{$row.total_hours|escape}</td>
        </tr>
        {/foreach}
    </tbody>
    <tfoot>
        <tr style="background: #f8f8f8; font-weight: bold;">
            <td style="padding: 10px;">Total</td>
            <td style="padding: 10px; text-align: right;">{$grand_total|string_format:"%.2f"}</td>
        </tr>
    </tfoot>
</table>
{/if}

<p style="margin-top: 20px;"><a href="/timesheet" class="btn">Back to My Timesheet</a></p>
</div>
{/block}
