{extends file="layout/main.tpl"}
{block name="content"}
<h1>Pending Approvals</h1>
<p style="color:#666; margin-bottom:20px;">Approve completed tasks and timesheet entries for your team.</p>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<h2 style="margin-top: 24px;">Pending Task Completions</h2>
{if empty($tasks)}
    <p>No completed tasks pending approval.</p>
{else}
<table class="data-table" style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px;">Task</th>
            <th style="padding: 10px;">Product</th>
            <th style="padding: 10px;">Assignee</th>
            <th style="padding: 10px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        {foreach $tasks as $t}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$t.title|escape}</td>
            <td style="padding: 10px;">{$t.product_name|escape}</td>
            <td style="padding: 10px;">{$t.assignee_email|escape}</td>
            <td style="padding: 10px;">
                <form method="post" action="/approval/approve/{$t.id}" style="display:inline;">
                    <input type="hidden" name="{$csrf}" value="{$hash}">
                    <button type="submit" class="btn">Approve</button>
                </form>
                <form method="post" action="/approval/reject/{$t.id}" style="display:inline; margin-left:8px;">
                    <input type="hidden" name="{$csrf}" value="{$hash}">
                    <input type="text" name="feedback" placeholder="Feedback (optional)" style="width:150px;">
                    <button type="submit" class="btn btn-secondary">Reject</button>
                </form>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}

<h2 style="margin-top: 32px;">Pending Timesheet Entries</h2>
{if empty($time_entries)}
    <p>No timesheet entries pending approval.</p>
{else}
<table class="data-table" style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px;">Employee</th>
            <th style="padding: 10px;">Product</th>
            <th style="padding: 10px;">Task</th>
            <th style="padding: 10px;">Date</th>
            <th style="padding: 10px;">Hours</th>
            <th style="padding: 10px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        {foreach $time_entries as $e}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$e.user_email|escape}</td>
            <td style="padding: 10px;">{$e.product_name|escape}</td>
            <td style="padding: 10px;">{$e.task_title|escape}</td>
            <td style="padding: 10px;">{$e.work_date|escape}</td>
            <td style="padding: 10px;">{$e.hours|escape}</td>
            <td style="padding: 10px;">
                <form method="post" action="/approval/timesheet/approve/{$e.id}" style="display:inline;">
                    <input type="hidden" name="{$csrf}" value="{$hash}">
                    <button type="submit" class="btn">Approve</button>
                </form>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}
{/block}
