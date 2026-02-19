{extends file="layout/main.tpl"}
{block name="content"}
{* Requires: user_email, is_super_admin from controller *}
<div class="profile-container">
    <h1>My Profile</h1>

    {if $success}
        <div class="alert alert-success">{$success|escape}</div>
    {/if}

    <div class="profile-card">
        <table class="profile-table">
            <tr><th>Employee ID</th><td>{$user.username|default:'—'|escape}</td></tr>
            <tr><th>Name</th><td>{$display_name|escape}</td></tr>
            <tr><th>Email</th><td>{$user.email|escape}</td></tr>
            <tr><th>Current Role</th><td>{$role_name|escape}</td></tr>
            <tr><th>Team Name</th><td>{$team_name|escape}</td></tr>
            <tr><th>Reporting Manager</th><td>{$reporting_manager_name|escape}</td></tr>
        </table>
        <div class="profile-actions">
            <a href="/profile/reset-password" class="btn btn-secondary">Reset My Password</a>
        </div>
    </div>
</div>
{/block}
