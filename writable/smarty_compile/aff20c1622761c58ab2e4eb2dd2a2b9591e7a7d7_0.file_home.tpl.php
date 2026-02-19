<?php
/* Smarty version 5.5.1, created on 2026-02-18 23:54:14
  from 'file:home.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.1',
  'unifunc' => 'content_69965126efafd1_99373191',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'aff20c1622761c58ab2e4eb2dd2a2b9591e7a7d7' => 
    array (
      0 => 'home.tpl',
      1 => 1771458840,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_69965126efafd1_99373191 (\Smarty\Template $_smarty_tpl) {
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

        <h1><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('title'), ENT_QUOTES, 'UTF-8', true);?>
</h1>
        <p><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('message'), ENT_QUOTES, 'UTF-8', true);?>
</p>

        <div class="features">
            <h3>Project Features</h3>
            <ul>
                <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('features'), 'feature');
$foreach0DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('feature')->value) {
$foreach0DoElse = false;
?>
                    <li><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('feature'), ENT_QUOTES, 'UTF-8', true);?>
</li>
                <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
            </ul>
        </div>

        <div class="year">
            <p>&copy; <?php echo $_smarty_tpl->getValue('year');?>
 Resource Timesheet Management System (RTMS). All rights reserved.</p>
        </div>
    </div>
</body>
</html>
<?php }
}
