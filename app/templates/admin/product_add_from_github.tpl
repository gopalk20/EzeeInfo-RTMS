{extends file="layout/main.tpl"}
{block name="content"}
<h1>Add Product from GitHub</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<p style="color:#666; margin-bottom:20px;">Paste a GitHub repository URL. The product will be created with the repository name. You can then edit it to set Team, Product Lead, and sync Issues as tasks.</p>

<form method="post" action="/admin/products/add-from-github" style="max-width: 600px;">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <div class="form-group">
        <label for="github_repo_url">GitHub Repository URL <span style="color:#991b1b;">*</span></label>
        <input type="url" name="github_repo_url" id="github_repo_url" value="{$github_repo_url|default:''|escape}" placeholder="https://github.com/owner/repo" required maxlength="512" style="width:100%; padding: 12px; font-size: 1rem;">
        <p style="color:#666; font-size:0.9em; margin-top:8px;">Example: https://github.com/microsoft/vscode</p>
    </div>
    <p style="margin-top: 24px;">
        <button type="submit" class="btn" style="background:#24292f;">Add Product from GitHub</button>
        <a href="/admin/products/manage" class="btn btn-secondary">Cancel</a>
    </p>
</form>
{/block}
