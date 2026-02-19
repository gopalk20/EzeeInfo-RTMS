<?php
/* Smarty version 5.5.1, created on 2026-02-18 23:44:16
  from 'file:about.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.1',
  'unifunc' => 'content_69964ed024fc85_17812403',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'bbe2877720b34bdd02a829d9ef4ddd8ef67fffac' => 
    array (
      0 => 'about.tpl',
      1 => 1771456170,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_69964ed024fc85_17812403 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\Users\\Public\\Documents\\php-codeigniter-smarty-mysql\\app\\templates';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('title'), ENT_QUOTES, 'UTF-8', true);?>
</title>
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
        .version-group {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .version-group h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .version-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
            color: #333;
        }
        .version-item:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #555;
        }
        .value {
            color: #667eea;
            font-weight: 500;
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

        <h1><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('title'), ENT_QUOTES, 'UTF-8', true);?>
</h1>

        <div class="version-group">
            <h3>System Information</h3>
            <div class="version-item">
                <span class="label">PHP Version:</span>
                <span class="value"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('php_version'), ENT_QUOTES, 'UTF-8', true);?>
</span>
            </div>
            <div class="version-item">
                <span class="label">CodeIgniter Version:</span>
                <span class="value"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('codeigniter_version'), ENT_QUOTES, 'UTF-8', true);?>
</span>
            </div>
            <div class="version-item">
                <span class="label">Smarty Version:</span>
                <span class="value"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('smarty_version'), ENT_QUOTES, 'UTF-8', true);?>
</span>
            </div>
            <div class="version-item">
                <span class="label">Database Driver:</span>
                <span class="value"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('mysql_version'), ENT_QUOTES, 'UTF-8', true);?>
</span>
            </div>
        </div>

        <div class="version-group" style="border-left-color: #764ba2;">
            <h3>Technology Stack</h3>
            <p style="padding: 10px 0; color: #333; line-height: 1.6;">
                This project demonstrates the integration of:
            </p>
            <ul style="padding: 0 0 0 20px; color: #555;">
                <li style="padding: 8px 0;"><strong>PHP 8.4</strong> - Latest PHP version with modern features</li>
                <li style="padding: 8px 0;"><strong>CodeIgniter 4</strong> - Lightweight PHP framework with excellent features</li>
                <li style="padding: 8px 0;"><strong>Smarty</strong> - Powerful template engine for PHP</li>
                <li style="padding: 8px 0;"><strong>MySQL/MySQLi</strong> - Reliable relational database</li>
            </ul>
        </div>
    </div>
</body>
</html>
<?php }
}
