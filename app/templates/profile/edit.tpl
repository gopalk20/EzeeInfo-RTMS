{extends file="layout/main.tpl"}
{block name="content"}
<div class="profile-container">
    <h1>Edit My Profile</h1>

    {if $success}
        <div class="alert alert-success">{$success|escape}</div>
    {/if}
    {if $errors}
        <div class="alert alert-error">
            <ul style="margin:0; padding-left:20px;">
                {foreach $errors as $err}
                <li>{$err|escape}</li>
                {/foreach}
            </ul>
        </div>
    {/if}

    <div class="profile-card">
        <form method="post" action="/profile/edit" class="profile-form">
            <input type="hidden" name="{$csrf}" value="{$hash}">
            <table class="profile-table">
                <tr>
                    <th><label for="first_name">First Name <span style="color:#991b1b;">*</span></label></th>
                    <td><input type="text" name="first_name" id="first_name" value="{$user.first_name|default:''|escape}" maxlength="128" required style="width:100%; padding:8px;"></td>
                </tr>
                <tr>
                    <th><label for="last_name">Last Name <span style="color:#991b1b;">*</span></label></th>
                    <td><input type="text" name="last_name" id="last_name" value="{$user.last_name|default:''|escape}" maxlength="128" required style="width:100%; padding:8px;"></td>
                </tr>
                <tr>
                    <th><label for="email">Email <span style="color:#991b1b;">*</span></label></th>
                    <td><input type="email" name="email" id="email" value="{$user.email|default:''|escape}" required maxlength="255" style="width:100%; padding:8px;"></td>
                </tr>
                <tr>
                    <th><label for="employee_id">Employee ID</label></th>
                    <td><input type="text" name="employee_id" id="employee_id" value="{$user.employee_id|default:($user.username|default:'')|escape}" maxlength="64" style="width:100%; padding:8px;" placeholder="e.g. EZEE176"></td>
                </tr>
            </table>
            <p style="color:#666; font-size:0.9em; margin-top:12px;">Role, Team, and Reporting Manager are managed by your administrator.</p>
            <div class="profile-actions" style="margin-top:20px;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/profile" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
{/block}
