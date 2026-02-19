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
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
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
        
        <nav>
            <a href="{$base_url}">Home</a>
            <a href="{$base_url}home/about">About</a>
        </nav>

        <p>{$description}</p>

        <h2>Project Stack</h2>
        <ul>
            <li><strong>PHP</strong> - Version 8.4+</li>
            <li><strong>CodeIgniter 4</strong> - Modern web framework</li>
            <li><strong>Smarty</strong> - Template engine</li>
            <li><strong>MySQL</strong> - Database server</li>
        </ul>

        <h2>Getting Started</h2>
        <ol>
            <li>Ensure all dependencies are installed</li>
            <li>Configure database settings in .env</li>
            <li>Create necessary database tables</li>
            <li>Run the development server</li>
        </ol>

        <footer style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666;">
            <p>&copy; 2026 - CodeIgniter 4 + Smarty + MySQL Project</p>
        </footer>
    </div>
</body>
</html>
