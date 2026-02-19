{extends file="layout/main.tpl"}
{block name="content"}
<div class="content-card">
<h1>Team Timesheet</h1>

<div style="margin-bottom: 20px;">
    <a href="/timesheet/team?period=daily{if $filter_team}&team={$filter_team|escape:url}{/if}" class="btn {if $period == 'daily'}btn-secondary{/if}">Daily</a>
    <a href="/timesheet/team?period=weekly{if $filter_team}&team={$filter_team|escape:url}{/if}" class="btn {if $period == 'weekly'}btn-secondary{/if}">Weekly</a>
    <a href="/timesheet/team?period=monthly{if $filter_team}&team={$filter_team|escape:url}{/if}" class="btn {if $period == 'monthly'}btn-secondary{/if}">Monthly</a>
</div>

<form method="get" action="" id="team-ts-form" style="margin-bottom: 20px; padding: 16px; background: #f8f8f8; border-radius: 6px;">
    <input type="hidden" name="period" value="{$period|escape}">
    <label for="team-filter" style="font-weight:500; margin-right:8px;">Department:</label>
    <select name="team" id="team-filter" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; margin-right: 16px;">
        <option value="">— All —</option>
        {foreach $teams as $t}
        <option value="{$t.name|escape}" {if $filter_team == $t.name}selected{/if}>{$t.name|escape}</option>
        {/foreach}
    </select>
    {if $period == 'daily'}
    <label for="date">Select date:</label>
    <input type="date" name="date" id="date" value="{$from|escape}" onchange="this.form.submit()" style="margin-left: 8px;">
    {elseif $period == 'weekly'}
    <label for="date">Select week:</label>
    <input type="date" name="date" id="date" value="{$from|escape}" onchange="this.form.submit()" style="margin-left: 8px;">
    {else}
    <label for="month">Select month:</label>
    <input type="month" name="month" id="month" value="{$month_value|escape}" onchange="this.form.submit()" style="margin-left: 8px;">
    {/if}
</form>

<p style="color:#666;">Period: {$from|escape} to {$to|escape}</p>

<div style="overflow-x: auto; margin-top: 24px;">
<table class="team-allocation-table" style="width:100%; border-collapse: collapse; min-width: 900px;">
    <thead>
        <tr style="background: #374151; color: white;">
            <th style="padding: 12px; text-align: left;">EMPLOYEE NAME</th>
            <th style="padding: 12px; text-align: left;">DEPARTMENT</th>
            <th style="padding: 12px; text-align: left;">ALLOCATION</th>
            <th style="padding: 12px; text-align: left;">BILLING ROLE</th>
            <th style="padding: 12px; text-align: right;">BILLING RATE (HR)</th>
            <th style="padding: 12px; text-align: left;">HOURS SPENT / ALLOCATED</th>
            <th style="padding: 12px; text-align: center;">ACTIONS</th>
        </tr>
    </thead>
    <tbody>
        {foreach $rows as $r}
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 12px;">
                <div style="font-weight: 500;">{$r.display_name|escape}</div>
                <div style="font-size: 0.85em; color: #6b7280;">{$r.role_name|escape}</div>
            </td>
            <td style="padding: 12px;">{$r.team_name|escape}</td>
            <td style="padding: 12px;">
                <div>{$r.allocation|escape}</div>
                <div style="font-size: 0.9em; color: #059669;">{$r.allocation_pct|escape} Allocation</div>
            </td>
            <td style="padding: 12px;">{$r.billing_role|escape}</td>
            <td style="padding: 12px; text-align: right;">{$r.billing_rate|string_format:"%.2f"}</td>
            <td style="padding: 12px; min-width: 180px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="flex: 1; height: 8px; background: #e5e7eb; border-radius: 4px;">
                        <div style="height: 100%; width: {$r.pct_used|string_format:"%.0f"}%; background: #059669; border-radius: 4px; max-width: 100%;"></div>
                    </div>
                    <span style="font-size: 0.9em; white-space: nowrap;">{$r.hours_spent|string_format:"%.0f"} hrs / {$r.hours_allocated|string_format:"%.0f"} hrs</span>
                </div>
            </td>
            <td style="padding: 12px; text-align: center;">
                <a href="/timesheet/team/details?user_id={$r.user_id}&from={$from|escape}&to={$to|escape}&period={$period|escape}{if $filter_team}&team={$filter_team|escape:url}{/if}" class="btn-link btn-sm">View</a>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
</div>

{if empty($rows)}
<p style="margin-top: 20px; color: #6b7280;">No team members with time entries in this period.</p>
{/if}

<p style="margin-top: 24px;"><a href="/timesheet" class="btn">My Timesheet</a> <a href="/approval" class="btn btn-secondary">Pending Approvals</a></p>
</div>

<style>
.team-allocation-table td, .team-allocation-table th { border: 1px solid #e5e7eb; }
.team-allocation-table tbody tr:hover { background: #f9fafb; }
</style>
{/block}
