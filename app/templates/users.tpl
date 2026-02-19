<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title|escape}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 900px;
            width: 100%;
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            font-size: 2em;
        }
        .navbar {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #667eea;
        }
        .navbar a {
            color: #667eea;
            text-decoration: none;
            margin-right: 20px;
            font-weight: 500;
            transition: color 0.3s;
        }
        .navbar a:hover {
            color: #764ba2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table thead {
            background-color: #f8f9fa;
        }
        table th {
            color: #667eea;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #667eea;
        }
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            color: #333;
        }
        table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <a href="/">Home</a>
            <a href="/users">Users</a>
            <a href="/about">About</a>
            {if isset($logged_in) && $logged_in}
                <a href="/profile">Profile</a>
                <a href="/logout">Logout</a>
            {else}
                <a href="/login">Login</a>
            {/if}
        </div>

        <h1>{$title|escape}</h1>

        {if isset($error) && $error}
            <div class="error">
                <strong>Note:</strong> {$error|escape}
            </div>
        {/if}

        {if count($users) > 0}
            <div class="info">
                <strong>Total Users:</strong> {$total_users}
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $users as $user}
                        <tr>
                            <td>{$user['id']|escape}</td>
                            <td>{$user['name']|escape}</td>
                            <td>{$user['email']|escape}</td>
                            <td>{$user['created_at']|escape}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        {else}
            <div class="no-data">
                <p>No users found. Please ensure the database is configured and the users table exists.</p>
            </div>
        {/if}
    </div>
</body>
</html>
