<?php
/* Smarty version 5.5.1, created on 2026-02-19 06:56:18
  from 'file:home.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.1',
  'unifunc' => 'content_6996b41259a527_08197013',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'aff20c1622761c58ab2e4eb2dd2a2b9591e7a7d7' => 
    array (
      0 => 'home.tpl',
      1 => 1771477120,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_6996b41259a527_08197013 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\Users\\Public\\Documents\\php-codeigniter-smarty-mysql\\app\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, true);
?>

<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_4312020726996b412576398_44712143', "content");
?>

<?php $_smarty_tpl->getInheritance()->endChild($_smarty_tpl, "layout/main.tpl", $_smarty_current_dir);
}
/* {block "content"} */
class Block_4312020726996b412576398_44712143 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\Users\\Public\\Documents\\php-codeigniter-smarty-mysql\\app\\templates';
?>

<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Control panel</p>
</div>
<?php if ((true && ($_smarty_tpl->hasVariable('success') && null !== ($_smarty_tpl->getValue('success') ?? null))) && $_smarty_tpl->getValue('success')) {?>
<div class="alert alert-success"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('success'), ENT_QUOTES, 'UTF-8', true);?>
</div>
<?php }?>

<div class="dashboard-cards">
    <a href="/timesheet" class="dash-card dash-card-teal">
        <div class="dash-card-icon">⏱</div>
        <div class="dash-card-content">
            <span class="dash-card-label">TIMESHEET</span>
            <span class="dash-card-link">Log Time Entry</span>
        </div>
    </a>
    <a href="/tasks" class="dash-card dash-card-orange">
        <div class="dash-card-icon">📋</div>
        <div class="dash-card-content">
            <span class="dash-card-label">TASK</span>
            <span class="dash-card-link">Go to Tasks</span>
            <?php if ($_smarty_tpl->getValue('my_task_count') > 0) {?><span class="dash-card-count"><?php echo $_smarty_tpl->getValue('my_task_count');?>
</span><?php }?>
        </div>
    </a>
    <?php if ((true && ($_smarty_tpl->hasVariable('user_role') && null !== ($_smarty_tpl->getValue('user_role') ?? null))) && $_smarty_tpl->getSmarty()->getModifierCallback('in_array')($_smarty_tpl->getValue('user_role'),array('Manager','Product Lead','Super Admin'))) {?>
    <a href="/approval" class="dash-card dash-card-purple">
        <div class="dash-card-icon">✈</div>
        <div class="dash-card-content">
            <span class="dash-card-label">PENDING APPROVAL</span>
            <span class="dash-card-desc">Waiting for approval</span>
            <span class="dash-card-count-large"><?php echo (($tmp = $_smarty_tpl->getValue('pending_count') ?? null)===null||$tmp==='' ? 0 ?? null : $tmp);?>
</span>
        </div>
    </a>
    <a href="/timesheet/team" class="dash-card dash-card-blue">
        <div class="dash-card-icon">👥</div>
        <div class="dash-card-content">
            <span class="dash-card-label">TEAM TIMESHEET</span>
            <span class="dash-card-link">View consolidated team entries</span>
        </div>
    </a>
    <?php }?>
</div>

<style>
.dashboard-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
.dash-card {
    display: flex; align-items: center; gap: 20px; padding: 24px;
    border-radius: 8px; text-decoration: none; color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s;
}
.dash-card:hover { transform: translateY(-2px); }
.dash-card-teal { background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); }
.dash-card-orange { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); }
.dash-card-purple { background: linear-gradient(135deg, #6f42c1 0%, #4a3f6e 100%); }
.dash-card-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
.dash-card-icon { font-size: 2.5rem; opacity: 0.9; }
.dash-card-content { flex: 1; }
.dash-card-label { font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; opacity: 0.9; }
.dash-card-link { display: block; margin-top: 4px; font-size: 0.9rem; text-decoration: underline; opacity: 0.95; }
.dash-card-desc { display: block; font-size: 0.85rem; opacity: 0.9; }
.dash-card-count { display: inline-block; background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; margin-left: 8px; }
.dash-card-count-large { display: block; font-size: 2.5rem; font-weight: 700; margin-top: 8px; }
</style>
<?php
}
}
/* {/block "content"} */
}
