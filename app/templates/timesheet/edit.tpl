{extends file="layout/main.tpl"}
{block name="content"}
<h1>Edit Time Entry</h1>

<form method="post" action="/timesheet/update/{$entry.id}">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <div class="form-group">
        <label>Task</label>
        <p><strong>{$task.title|escape}</strong> ({$task.product_name|default:''|escape})</p>
    </div>
    <div class="form-group">
        <label>Work Date</label>
        <p>{$entry.work_date|escape}</p>
    </div>
    <div class="form-group">
        <label for="hours">Hours</label>
        <input type="number" name="hours" id="hours" step="0.25" min="0.25" max="24" value="{$entry.hours|escape}" required>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" rows="2">{$entry.description|default:''|escape}</textarea>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_rework" value="1" {if $entry.is_rework}checked{/if}> Mark as rework</label>
    </div>
    <p><button type="submit" class="btn">Save Changes</button> <a href="/timesheet{if $back_to_sheet_date}?date={$back_to_sheet_date|escape}{/if}" class="btn btn-secondary">Cancel</a></p>
</form>
{/block}
