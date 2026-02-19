{extends file="layout/main.tpl"}
{block name="content"}
<h1>Edit Task</h1>

<form method="post" action="/products/{$product.id}/tasks/edit/{$task.id}">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" value="{$task.title|escape}" required maxlength="512" style="width:100%;">
    </div>
    <div class="form-group">
        <label for="status">Status</label>
        <select name="status" id="status">
            <option value="To Do" {if $task.status == 'To Do'}selected{/if}>To Do</option>
            <option value="In Progress" {if $task.status == 'In Progress'}selected{/if}>In Progress</option>
            <option value="Completed" {if $task.status == 'Completed'}selected{/if}>Completed</option>
            <option value="Rework Requested" {if $task.status == 'Rework Requested'}selected{/if}>Rework Requested</option>
        </select>
    </div>
    <div class="form-group">
        <label for="assignee_id">Assignee</label>
        <select name="assignee_id" id="assignee_id">
            <option value="">— No assignee —</option>
            {foreach $all_users as $u}
            <option value="{$u.id}" {if $task.assignee_id == $u.id}selected{/if}>{($u.first_name|default:'')|cat:' '|cat:($u.last_name|default:'')|escape} ({$u.email|escape})</option>
            {/foreach}
        </select>
    </div>
    <p><button type="submit" class="btn">Save</button> <a href="/products/view/{$product.id}" class="btn btn-secondary">Cancel</a></p>
</form>
{/block}
