{extends file="layout/main.tpl"}
{block name="content"}
<div class="content-card">
<h1>My Timesheet</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<h2 style="margin-top: 24px;">My Time Entries (This Month)</h2>
{if empty($entries)}
    <p style="color: #666;">No time entries yet. Log time below to add your first entry.</p>
{else}
    <table class="data-table" style="width:100%; border-collapse: collapse; margin-top: 12px;">
        <thead>
            <tr style="background: #f0f0f0;">
                <th style="padding: 10px; text-align: left;">Date</th>
                <th style="padding: 10px; text-align: left;">Task / Product</th>
                <th style="padding: 10px; text-align: left;">Description</th>
                <th style="padding: 10px; text-align: left;">Hours</th>
                <th style="padding: 10px; text-align: left;">Rework</th>
                <th style="padding: 10px; text-align: left;">Status</th>
                <th style="padding: 10px; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            {foreach $entries as $e}
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;">{$e.work_date|escape}</td>
                <td style="padding: 10px;">{$e.task_title|default:'—'|escape} <span style="color:#666;">({$e.product_name|default:'—'|escape})</span></td>
                <td style="padding: 10px;">{$e.description|default:'—'|escape}</td>
                <td style="padding: 10px;">{$e.hours|escape}</td>
                <td style="padding: 10px;">{if $e.is_rework}Yes{else}—{/if}</td>
                <td style="padding: 10px;">{if ($e.status|default:'') == 'approved'}Approved{else}Pending Approval{/if}</td>
                <td style="padding: 10px;">{if ($e.status|default:'pending_approval') == 'pending_approval'}<a href="/timesheet/edit/{$e.id}" class="btn-link">Edit</a>{else}—{/if}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>
{/if}

<h2 style="margin-top: 32px;">Log Time</h2>
{if empty($tasks) && empty($products)}
    <p>No tasks or products available to log time.</p>
    <p style="color:#666; font-size:0.9em;">Products must be <strong>mapped to your team</strong> by Super Admin (Manage Products → Edit → Team). New products from GitHub need this step before they appear here.</p>
    <p><a href="/tasks" class="btn">View My Tasks</a> <a href="/products" class="btn btn-secondary">View Products</a></p>
{else}
<p style="margin-bottom: 12px;">
    <a href="/timesheet?flow=task{if $default_work_date}&amp;date={$default_work_date|escape}{/if}" class="btn {if $flow_mode == 'task'}btn-primary{else}btn-secondary{/if}">By Task</a>
    <a href="/timesheet?flow=product{if $default_work_date}&amp;date={$default_work_date|escape}{/if}" class="btn {if $flow_mode == 'product'}btn-primary{else}btn-secondary{/if}">By Product</a>
</p>
<form method="post" action="{$log_action_url|escape}" id="timesheet-log-form">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    {if $flow_mode == 'product'}
    <div class="form-group" id="product-select-group">
        <label for="product_id">Product</label>
        <select name="product_id" id="product_id">
            <option value="">-- Select Product --</option>
            {foreach $products as $p}
            <option value="{$p.id}">{$p.name|escape}</option>
            {/foreach}
        </select>
    </div>
    <div class="form-group" id="task-by-product-group">
        <label for="task_id_product">Task</label>
        <select name="task_id" id="task_id_product" required>
            <option value="">-- Select Product first --</option>
        </select>
    </div>
    {else}
    <div class="form-group">
        <label for="task_id">Task</label>
        <select name="task_id" id="task_id" required>
            <option value="">-- Select Task --</option>
            {foreach $tasks as $t}
            <option value="{$t.id}">{$t.title|escape} ({$t.product_name|escape})</option>
            {/foreach}
        </select>
    </div>
    {/if}
    <div class="form-group">
        <label for="work_date">Work Date</label>
        <input type="date" name="work_date" id="work_date" value="{if isset($request.work_date)}{$request.work_date|escape}{else}{$default_work_date|escape}{/if}" required>
    </div>
    <div class="form-group">
        <label for="hours">Hours</label>
        <input type="number" name="hours" id="hours" step="0.25" min="0.25" max="24" placeholder="e.g. 2.5" required>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" rows="2" placeholder="What did you work on?"></textarea>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_rework" value="1"> Mark as rework</label>
    </div>
    <p style="color: #666; font-size: 0.9em;">Daily limit: 24 hours max per day</p>
    <button type="submit" class="btn">Save Time Entry</button>
</form>
{if $flow_mode == 'product'}
<script id="tasks-by-product-data" type="application/json">{$tasks_by_product_json|escape:'html'}</script>
<script>
(function() {
    var el = document.getElementById('tasks-by-product-data');
    var tasksByProduct = el ? JSON.parse(el.textContent || '{}') : {};
    var productSelect = document.getElementById('product_id');
    var taskSelect = document.getElementById('task_id_product');
    if (!productSelect || !taskSelect) return;
    productSelect.addEventListener('change', function() {
        var pid = parseInt(this.value, 10);
        taskSelect.innerHTML = '<option value="">-- Select Task --</option>';
        if (pid && tasksByProduct[pid]) {
            tasksByProduct[pid].forEach(function(t) {
                var opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.title + ' (' + (t.product_name || '') + ')';
                taskSelect.appendChild(opt);
            });
        }
    });
    document.getElementById('timesheet-log-form').addEventListener('submit', function() {
        var t = document.getElementById('task_id_product');
        if (t && t.name === 'task_id') { t.name = 'task_id'; }
    });
})();
</script>
{/if}
{/if}

<p style="margin-top: 24px;"><a href="/timesheet/view?period=daily{if $default_work_date}&amp;date={$default_work_date|escape}{/if}" class="btn">View Daily</a> <a href="/timesheet/view?period=weekly{if $default_work_date}&amp;date={$default_work_date|escape}{/if}" class="btn btn-secondary">View Weekly</a> <a href="/timesheet/view?period=monthly" class="btn btn-secondary">View Monthly</a></p>
</div>
{/block}
