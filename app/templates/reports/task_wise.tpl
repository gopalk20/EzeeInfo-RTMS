{extends file="layout/main.tpl"}
{block name="content"}
<h1>Task-wise Time Report</h1>
<p style="color:#666; margin-bottom:16px;">Period: {$from|escape} to {$to|escape}</p>
<p>
    <a href="/reports/export/task-wise?from={$from|escape}&to={$to|escape}" class="btn">Export CSV</a>
    <a href="/reports?from={$from|escape}&to={$to|escape}" class="btn btn-secondary">Back to Reports</a>
</p>

{if empty($rows)}
    <p>No time entries for this period. Try selecting a different date range on the <a href="/reports">Reports</a> page.</p>
{else}
<table class="data-table" style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px;">Task</th>
            <th style="padding: 10px;">Product</th>
            <th style="padding: 10px;">Status</th>
            <th style="padding: 10px;">Total Hours</th>
            <th style="padding: 10px;">Rework Hours</th>
            <th style="padding: 10px;">Rework %</th>
        </tr>
    </thead>
    <tbody>
        {foreach $rows as $r}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$r.title|escape}</td>
            <td style="padding: 10px;">{$r.product_name|escape}</td>
            <td style="padding: 10px;">{$r.status|escape}</td>
            <td style="padding: 10px;">{$r.total_hours|escape}</td>
            <td style="padding: 10px;">{$r.rework_hours|escape}</td>
            <td style="padding: 10px;">{$r.rework_pct|escape}%</td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}
{/block}
