{extends file="layout/main.tpl"}
{block name="content"}
<h1>My Tasks</h1>

{if empty($tasks)}
    <p>No tasks yet. Sync from GitHub (Products → your product → Sync from GitHub Issues) or ask your Product Lead to add tasks.</p>
    <p><a href="/products" class="btn">View Products</a> <a href="/timesheet" class="btn btn-secondary">Log Time</a></p>
{else}
    <table class="data-table" style="width:100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background: #f0f0f0;">
                <th style="padding: 10px; text-align: left;">Task</th>
                <th style="padding: 10px; text-align: left;">Product</th>
                <th style="padding: 10px; text-align: left;">Status</th>
                <th style="padding: 10px; text-align: left;">Action</th>
            </tr>
        </thead>
        <tbody>
            {foreach $tasks as $t}
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;">{$t.title|escape}</td>
                <td style="padding: 10px;">{$t.product_name|escape}</td>
                <td style="padding: 10px;">{$t.status|escape}</td>
                <td style="padding: 10px;">
                    <a href="/timesheet?task_id={$t.id}" class="btn">Log Time</a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    <p style="margin-top: 20px;"><a href="/timesheet" class="btn">My Timesheet</a></p>
{/if}
{/block}
