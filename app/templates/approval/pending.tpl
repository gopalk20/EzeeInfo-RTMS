{extends file="layout/main.tpl"}
{block name="content"}
<style>
.btn-icon { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0; border: none; border-radius: 6px; cursor: pointer; }
.btn-icon.approve { background: #10b981; color: white; }
.btn-icon.approve:hover { background: #059669; }
.btn-icon.reject { background: #ef4444; color: white; }
.btn-icon.reject:hover { background: #dc2626; }
.btn-icon svg { width: 16px; height: 16px; }
.approval-actions { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.approval-actions form { display: inline; }
</style>
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
                <div class="approval-actions">
                    <form method="post" action="/approval/approve/{$t.id}">
                        <input type="hidden" name="{$csrf}" value="{$hash}">
                        <button type="submit" class="btn-icon approve" title="Approve">
                            <svg viewBox="0 0 16 16" fill="currentColor"><path d="M13.78 4.22a.75.75 0 0 1 0 1.06l-7.25 7.25a.75.75 0 0 1-1.06 0L2.22 9.28a.75.75 0 1 1 1.06-1.06L6 10.94l6.72-6.72a.75.75 0 0 1 1.06 0Z"/></svg>
                        </button>
                    </form>
                    <form method="post" action="/approval/reject/{$t.id}">
                        <input type="hidden" name="{$csrf}" value="{$hash}">
                        <input type="text" name="feedback" placeholder="Feedback (optional)" style="width:120px; padding:4px 8px; font-size:0.85rem; border:1px solid #e2e8f0; border-radius:4px;" title="Optional feedback for reject">
                        <button type="submit" class="btn-icon reject" title="Reject">
                            <svg viewBox="0 0 16 16" fill="currentColor"><path d="M3.72 3.72a.75.75 0 0 1 1.06 0L8 6.94l3.22-3.22a.75.75 0 1 1 1.06 1.06L9.06 8l3.22 3.22a.75.75 0 1 1-1.06 1.06L8 9.06l-3.22 3.22a.75.75 0 0 1-1.06-1.06L6.94 8 3.72 4.78a.75.75 0 0 1 0-1.06Z"/></svg>
                        </button>
                    </form>
                </div>
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
                <div class="approval-actions">
                    <form method="post" action="/approval/timesheet/approve/{$e.id}">
                        <input type="hidden" name="{$csrf}" value="{$hash}">
                        <button type="submit" class="btn-icon approve" title="Approve">
                            <svg viewBox="0 0 16 16" fill="currentColor"><path d="M13.78 4.22a.75.75 0 0 1 0 1.06l-7.25 7.25a.75.75 0 0 1-1.06 0L2.22 9.28a.75.75 0 1 1 1.06-1.06L6 10.94l6.72-6.72a.75.75 0 0 1 1.06 0Z"/></svg>
                        </button>
                    </form>
                    <form method="post" action="/approval/timesheet/reject/{$e.id}" style="display:inline-flex; align-items:center; gap:6px;">
                        <input type="hidden" name="{$csrf}" value="{$hash}">
                        <input type="text" name="feedback" placeholder="Feedback (optional)" style="width:120px; padding:4px 8px; font-size:0.85rem; border:1px solid #e2e8f0; border-radius:4px;" title="Optional feedback included in rejection email">
                        <button type="submit" class="btn-icon reject" title="Reject">
                            <svg viewBox="0 0 16 16" fill="currentColor"><path d="M3.72 3.72a.75.75 0 0 1 1.06 0L8 6.94l3.22-3.22a.75.75 0 1 1 1.06 1.06L9.06 8l3.22 3.22a.75.75 0 1 1-1.06 1.06L8 9.06l-3.22 3.22a.75.75 0 0 1-1.06-1.06L6.94 8 3.72 4.78a.75.75 0 0 1 0-1.06Z"/></svg>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}

<h2 style="margin-top: 32px;">Approved Task Completions</h2>
{if empty($approved_tasks)}
    <p>No approved tasks.</p>
{else}
<table class="data-table" style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background: #ecfdf5;">
            <th style="padding: 10px;">Task</th>
            <th style="padding: 10px;">Product</th>
            <th style="padding: 10px;">Assignee</th>
            <th style="padding: 10px;">Approved At</th>
        </tr>
    </thead>
    <tbody>
        {foreach $approved_tasks as $t}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$t.title|escape}</td>
            <td style="padding: 10px;">{$t.product_name|escape}</td>
            <td style="padding: 10px;">{$t.assignee_email|escape}</td>
            <td style="padding: 10px;">{$t.approved_at|escape}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}

<h2 style="margin-top: 32px;">Approved Timesheet Entries</h2>
{if empty($approved_time_entries)}
    <p>No approved timesheet entries.</p>
{else}
<table class="data-table" style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background: #ecfdf5;">
            <th style="padding: 10px;">Employee</th>
            <th style="padding: 10px;">Product</th>
            <th style="padding: 10px;">Task</th>
            <th style="padding: 10px;">Date</th>
            <th style="padding: 10px;">Hours</th>
        </tr>
    </thead>
    <tbody>
        {foreach $approved_time_entries as $e}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$e.user_email|escape}</td>
            <td style="padding: 10px;">{$e.product_name|escape}</td>
            <td style="padding: 10px;">{$e.task_title|escape}</td>
            <td style="padding: 10px;">{$e.work_date|escape}</td>
            <td style="padding: 10px;">{$e.hours|escape}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}
{/block}
