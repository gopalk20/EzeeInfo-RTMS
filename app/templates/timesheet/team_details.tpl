{extends file="layout/main.tpl"}
{block name="content"}
<div class="content-card">
<h1>Time Entries: {$display_name|escape}</h1>
<p style="color:#666;">Period: {$from|escape} to {$to|escape}</p>
<p><a href="/timesheet/team?period={$period|escape}{if $period == 'monthly'}&month={$month_value|escape}{elseif $period == 'daily'}&date={$from|escape}{elseif $period == 'weekly'}&date={$from|escape}{/if}">← Back to Team Timesheet</a></p>

{if empty($entries)}
<p style="margin-top: 20px;">No time entries for this period.</p>
{else}
<table class="data-table" style="width:100%; border-collapse: collapse; margin-top: 16px;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px;">Date</th>
            <th style="padding: 10px;">Project</th>
            <th style="padding: 10px;">Task</th>
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
</div>
{/block}
