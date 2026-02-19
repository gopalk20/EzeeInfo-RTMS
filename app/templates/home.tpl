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
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 800px;
            width: 90%;
        }
        h1 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 2.5em;
        }
        p {
            color: #333;
            font-size: 1.1em;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .features {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 30px 0;
            border-radius: 5px;
        }
        .features h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .features ul {
            list-style: none;
        }
        .features li {
            padding: 10px 0;
            color: #555;
            border-bottom: 1px solid #e0e0e0;
        }
        .features li:last-child {
            border-bottom: none;
        }
        .features li:before {
            content: "✓ ";
            color: #667eea;
            font-weight: bold;
            margin-right: 10px;
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
        .year {
            text-align: center;
            margin-top: 40px;
            color: #999;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <a href="/">Home</a>
            <a href="/users">Users</a>
            <a href="/about">About</a>
        </div>

        <h1>{$title|escape}</h1>
        <p>{$message|escape}</p>

        <div class="features">
            <h3>Project Features</h3>
            <ul>
                {foreach $features as $feature}
                    <li>{$feature|escape}</li>
                {/foreach}
            </ul>
        </div>

        <div class="year">
            <p>&copy; {$year} Resource Timesheet Management System (RTMS). All rights reserved.</p>
        </div>
    </div>
</body>
</html>
