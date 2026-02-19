<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - EzeeInfo Timesheet Entry</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #e0e2e8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .brand {
            text-align: center;
            margin-bottom: 24px;
        }
        .brand-text {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }
        .brand-text .accent { color: #6f42c1; }
        .brand-dot { color: #dc3545; font-size: 1.2em; }
        .login-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }
        .login-heading {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 24px;
            font-weight: 400;
        }
        .input-wrap {
            position: relative;
            margin-bottom: 20px;
        }
        .input-wrap input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            border: 1px solid #b8d4e8;
            border-radius: 6px;
            background: #f0f8ff;
            font-size: 1rem;
            color: #333;
        }
        .input-wrap input::placeholder { color: #8a9ba8; }
        .input-wrap input:focus {
            outline: none;
            border-color: #6f42c1;
            background: #fff;
        }
        .input-wrap .icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #8a9ba8;
            font-size: 1rem;
            pointer-events: none;
        }
        .forgot-link {
            display: block;
            text-align: right;
            margin-bottom: 20px;
            color: #6f42c1;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .forgot-link:hover { text-decoration: underline; }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #6f42c1;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-login:hover { background: #5a32a3; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .alert-error { background: #fee; color: #c00; border: 1px solid #fcc; }
        .alert-success { background: #efe; color: #060; border: 1px solid #cfc; }
        .footer-text { margin-top: 32px; color: #999; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="brand">
        <span class="brand-text"><span class="accent">Ezee</span>Info - Timesheet Entry</span>
    </div>
    <div class="login-card">
        <p class="login-heading">Log in to start your session</p>

        {if isset($success) && $success}
            <div class="alert alert-success">{$success|escape}</div>
        {/if}
        {if $error}
            <div class="alert alert-error">{$error|escape}</div>
        {/if}
        {if isset($errors.email)}
            <div class="alert alert-error">{$errors.email|escape}</div>
        {/if}

        <form method="post" action="/">
            {if isset($csrf) && isset($hash)}
            <input type="hidden" name="{$csrf}" value="{$hash}">
            {/if}
            <div class="input-wrap">
                <input type="email" id="email" name="email" value="{$email|escape}" placeholder="your.email@example.com" required autofocus>
                <span class="icon">&#9993;</span>
            </div>
            <div class="input-wrap">
                <input type="password" id="password" name="password" placeholder="••••••••••" required>
                <span class="icon">&#128274;</span>
            </div>
            <a href="#" class="forgot-link">Forgot Password</a>
            <button type="submit" class="btn-login">Log In</button>
        </form>
    </div>
    <p class="footer-text">Copyright © {$year|default:'2026'|escape} Ezee Info Solutions. All rights reserved.</p>
</body>
</html>
