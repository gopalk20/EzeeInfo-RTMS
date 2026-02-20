{extends file="layout/main.tpl"}
{block name="content"}
<div class="content-card">
<h1>Resource Costing</h1>
<p style="color:#666; margin-bottom:20px;">User costing vs Project costing for the selected period. Configure monthly cost per user in <a href="/admin/users">Manage Users</a> &gt; Edit User (Super Admin only).</p>

<form method="get" action="/costing" style="margin-bottom: 24px; padding: 16px; background: #f8f8f8; border-radius: 6px;">
    <label for="from">From:</label>
    <input type="date" name="from" id="from" value="{$from|escape}" style="margin-right: 16px; padding: 8px;">
    <label for="to">To:</label>
    <input type="date" name="to" id="to" value="{$to|escape}" style="margin-right: 16px; padding: 8px;">
    <button type="submit" class="btn btn-sm">Apply</button>
</form>

<h2 style="margin-top: 32px;">User Costing</h2>
<p style="color:#666; margin-bottom:12px;">Cost per user for the period (hours × hourly rate). Hourly = Monthly Cost / (Working Days × Standard Hours).</p>
<table class="data-table" style="width:100%; border-collapse: collapse; margin-bottom: 32px;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px;">User</th>
            <th style="padding: 10px; text-align: right;">Hours</th>
            <th style="padding: 10px; text-align: right;">Monthly Cost</th>
            <th style="padding: 10px; text-align: right;">Hourly Rate</th>
            <th style="padding: 10px; text-align: right;">Period Cost</th>
        </tr>
    </thead>
    <tbody>
        {foreach $user_costing as $r}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$r.first_name|escape} {$r.last_name|escape} ({$r.email|escape})</td>
            <td style="padding: 10px; text-align: right;">{$r.total_hours|string_format:"%.1f"}</td>
            <td style="padding: 10px; text-align: right;">{$r.monthly_cost|string_format:"%.2f"}</td>
            <td style="padding: 10px; text-align: right;">{$r.hourly_cost|string_format:"%.2f"}</td>
            <td style="padding: 10px; text-align: right;">{$r.period_cost|string_format:"%.2f"}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
{if empty($user_costing)}
<p style="color:#666;">No time entries in this period.</p>
{/if}

<h2 style="margin-top: 32px;">Project Costing</h2>
<p style="color:#666; margin-bottom:12px;">Cost per project for the period (sum of user costs attributed to each project).</p>
<table class="data-table" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px;">Project</th>
            <th style="padding: 10px; text-align: right;">Hours</th>
            <th style="padding: 10px; text-align: right;">Period Cost</th>
        </tr>
    </thead>
    <tbody>
        {foreach $project_costing as $r}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$r.product_name|escape}</td>
            <td style="padding: 10px; text-align: right;">{$r.total_hours|string_format:"%.1f"}</td>
            <td style="padding: 10px; text-align: right;">{$r.period_cost|string_format:"%.2f"}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
{if empty($project_costing)}
<p style="color:#666;">No project time in this period.</p>
{/if}

<p style="margin-top: 24px;"><a href="/admin/users" class="btn btn-secondary">Manage Users (configure cost)</a></p>
</div>
{/block}
