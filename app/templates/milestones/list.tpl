{extends file="layout/main.tpl"}
{block name="content"}
<h1>Milestones</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

{if empty($milestones)}
    <p>No milestones. Milestones are created by Product Lead or Manager per product.</p>
{else}
<table class="data-table" style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px;">Milestone</th>
            <th style="padding: 10px;">Product</th>
            <th style="padding: 10px;">Due Date</th>
            <th style="padding: 10px;">Status</th>
        </tr>
    </thead>
    <tbody>
        {foreach $milestones as $m}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$m.name|escape}</td>
            <td style="padding: 10px;">{$m.product_name|escape}</td>
            <td style="padding: 10px;">{$m.due_date|escape}</td>
            <td style="padding: 10px;">{$m.release_status|escape}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}
{/block}
