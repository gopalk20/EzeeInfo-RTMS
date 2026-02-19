{extends file="layout/main.tpl"}
{block name="content"}
<h1>Add New User</h1>

{if $errors}
    {foreach $errors as $err}
        <div class="alert alert-error">{$err|escape}</div>
    {/foreach}
{/if}

<form method="post" action="/admin/users/add" class="profile-form">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <div class="form-group">
        <label for="username">Username *</label>
        <input type="text" id="username" name="username" required maxlength="64">
    </div>
    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="first_name">First Name *</label>
        <input type="text" id="first_name" name="first_name" required maxlength="128">
    </div>
    <div class="form-group">
        <label for="last_name">Last Name *</label>
        <input type="text" id="last_name" name="last_name" required maxlength="128">
    </div>
    <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="text" id="phone" name="phone" maxlength="32">
    </div>
    <div class="form-group">
        <label for="role_id">Current Role *</label>
        <select id="role_id" name="role_id" required>
            <option value="">-- Select Role --</option>
            {foreach $roles as $r}
                <option value="{$r.id}">{$r.name|escape}</option>
            {/foreach}
        </select>
    </div>
    <div class="form-group">
        <label for="team_id">Team Name *</label>
        <select id="team_id" name="team_id" required>
            <option value="">-- Select Team --</option>
            {foreach $teams as $t}
                <option value="{$t.id}">{$t.name|escape}</option>
            {/foreach}
        </select>
    </div>
    <div class="form-group">
        <label for="password">Password *</label>
        <input type="password" id="password" name="password" required minlength="8">
    </div>
    <button type="submit" class="btn">Add User</button>
    <a href="/admin/users" class="btn btn-link">Cancel</a>
</form>
{/block}
