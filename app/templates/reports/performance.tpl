{extends file="layout/main.tpl"}
{block name="content"}
<h1>Performance & Rework Impact</h1>
<p style="color:#666;">Period: {$from|escape} to {$to|escape} · Rework % = (Rework Hours / Total Hours) × 100</p>
<p><a href="/reports?from={$from|escape}&to={$to|escape}" class="btn btn-secondary">Back to Reports</a></p>

{if empty($rows)}
    <p>No time entries for this period. Try selecting a different date range on the <a href="/reports">Reports</a> page.</p>
{else}
<table class="data-table" style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px;">Employee</th>
            <th style="padding: 10px;">Total Hours</th>
            <th style="padding: 10px;">Rework Hours</th>
            <th style="padding: 10px;">Rework %</th>
            <th style="padding: 10px;">Tasks</th>
        </tr>
    </thead>
    <tbody>
        {foreach $rows as $r}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$r.email|escape}</td>
            <td style="padding: 10px;">{$r.total_hours|escape}</td>
            <td style="padding: 10px;">{$r.rework_hours|escape}</td>
            <td style="padding: 10px;">{$r.rework_pct|escape}%</td>
            <td style="padding: 10px;">{$r.task_count|escape}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}
{/block}
