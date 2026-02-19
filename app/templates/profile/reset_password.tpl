<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title|escape} - RTMS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { background: white; border-radius: 10px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); padding: 40px; max-width: 500px; margin: 0 auto; }
        .navbar { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #667eea; }
        .navbar a { color: #667eea; text-decoration: none; margin-right: 15px; }
        h1 { color: #667eea; margin-bottom: 20px; }
        .alert-error { padding: 12px; background: #fee; color: #c00; border-radius: 6px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
        .btn { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer; margin-right: 10px; }
        .btn-link { background: transparent; color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <a href="/">Home</a>
            <a href="/profile">Profile</a>
            <a href="/logout">Logout</a>
        </div>
<div class="reset-password-container">
    <h1>Reset Password</h1>

    {if isset($errors.current_password)}
        <div class="alert alert-error">{$errors.current_password|escape}</div>
    {/if}
    {if $errors}
        {foreach $errors as $key => $err}
            {if $key != 'current_password'}
                <div class="alert alert-error">{$err|escape}</div>
            {/if}
        {/foreach}
    {/if}

    <form method="post" action="/profile/reset-password" class="profile-form">
        <input type="hidden" name="{$csrf}" value="{$hash}">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required minlength="8">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
        </div>
        <button type="submit" class="btn">Update Password</button>
        <a href="/profile" class="btn btn-link">Cancel</a>
    </form>
    </div>
</body>
</html>
