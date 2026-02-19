{extends file="layout/main.tpl"}
{block name="content"}
<h1>Resource Costing</h1>
<p style="color:#666; margin-bottom:20px;">Manager only. BR-5: Hourly Cost = Monthly Cost / (Working Days × Standard Hours).</p>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<p>Working Days: {$working_days}, Standard Hours: {$standard_hours}</p>

<table class="data-table" style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 10px;">User</th>
            <th style="padding: 10px;">Monthly Cost</th>
            <th style="padding: 10px;">Hourly Cost</th>
            <th style="padding: 10px;">Action</th>
        </tr>
    </thead>
    <tbody>
        {foreach $costs as $c}
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">{$c.first_name|escape} {$c.last_name|escape} ({$c.email|escape})</td>
            <td style="padding: 10px;">{$c.monthly_cost|escape}</td>
            <td style="padding: 10px;">{$c.hourly_cost|escape}</td>
            <td style="padding: 10px;">
                <form method="post" action="/costing/save" style="display:inline;">
                    <input type="hidden" name="{$csrf}" value="{$hash}">
                    <input type="hidden" name="user_id" value="{$c.user_id}">
                    <input type="number" name="monthly_cost" value="{$c.monthly_cost}" step="0.01" min="0" style="width:100px;">
                    <button type="submit" class="btn">Update</button>
                </form>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

<h2 style="margin-top:30px;">Add Cost for User</h2>
<form method="post" action="/costing/save">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <div class="form-group">
        <label>User</label>
        <select name="user_id" required>
            <option value="">-- Select --</option>
            {foreach $users as $u}
            <option value="{$u.id}">{$u.email|escape} ({$u.first_name|escape} {$u.last_name|escape})</option>
            {/foreach}
        </select>
    </div>
    <div class="form-group">
        <label>Monthly Cost</label>
        <input type="number" name="monthly_cost" step="0.01" min="0" required>
    </div>
    <button type="submit" class="btn">Save</button>
</form>
{/block}
