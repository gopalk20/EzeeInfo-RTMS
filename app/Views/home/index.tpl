<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .info {
            background-color: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
        nav {
            margin: 20px 0;
        }
        nav a {
            margin-right: 15px;
            text-decoration: none;
            color: #007bff;
        }
        nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{$title}</h1>
        
        <div class="info">
            <p>{$message}</p>
        </div>

        <nav>
            <a href="{$base_url}">Home</a>
            <a href="{$base_url}home/about">About</a>
        </nav>

        <p>This page demonstrates the integration of:</p>
        <ul>
            <li><strong>CodeIgniter 4</strong> - A powerful PHP web framework</li>
            <li><strong>Smarty</strong> - A template engine for PHP</li>
            <li><strong>MySQL</strong> - A reliable SQL database</li>
        </ul>

        <footer style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666;">
            <p>&copy; {$year} - Powered by CodeIgniter 4 with Smarty Template Engine</p>
        </footer>
    </div>
</body>
</html>
