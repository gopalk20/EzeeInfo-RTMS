<?php
/* Smarty version 5.5.1, created on 2026-02-18 23:53:47
  from 'file:users.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.1',
  'unifunc' => 'content_6996510b7f2c17_83985863',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4b5631f1a89417da50c16a309e95271fab6330fa' => 
    array (
      0 => 'users.tpl',
      1 => 1771456159,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_6996510b7f2c17_83985863 (\Smarty\Template $_smarty_tpl) {
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
        </div>

        <h1><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('title'), ENT_QUOTES, 'UTF-8', true);?>
</h1>

        <?php if ((true && ($_smarty_tpl->hasVariable('error') && null !== ($_smarty_tpl->getValue('error') ?? null))) && $_smarty_tpl->getValue('error')) {?>
            <div class="error">
                <strong>Note:</strong> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('error'), ENT_QUOTES, 'UTF-8', true);?>

            </div>
        <?php }?>

        <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('users')) > 0) {?>
            <div class="info">
                <strong>Total Users:</strong> <?php echo $_smarty_tpl->getValue('total_users');?>

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
                    <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('users'), 'user');
$foreach0DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('user')->value) {
$foreach0DoElse = false;
?>
                        <tr>
                            <td><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['id'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                            <td><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['name'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                            <td><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['email'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                            <td><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['created_at'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                        </tr>
                    <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-data">
                <p>No users found. Please ensure the database is configured and the users table exists.</p>
            </div>
        <?php }?>
    </div>
</body>
</html>
<?php }
}
