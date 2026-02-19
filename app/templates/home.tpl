{extends file="layout/main.tpl"}
{block name="content"}
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Control panel</p>
</div>
{if isset($success) && $success}
<div class="alert alert-success">{$success|escape}</div>
{/if}

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
            {if $my_task_count > 0}<span class="dash-card-count">{$my_task_count}</span>{/if}
        </div>
    </a>
    {if isset($user_role) && in_array($user_role, ['Manager', 'Product Lead', 'Super Admin'])}
    <a href="/approval" class="dash-card dash-card-purple">
        <div class="dash-card-icon">✈</div>
        <div class="dash-card-content">
            <span class="dash-card-label">PENDING APPROVAL</span>
            <span class="dash-card-desc">Waiting for approval</span>
            <span class="dash-card-count-large">{$pending_count|default:0}</span>
        </div>
    </a>
    <a href="/timesheet/team" class="dash-card dash-card-blue">
        <div class="dash-card-icon">👥</div>
        <div class="dash-card-content">
            <span class="dash-card-label">TEAM TIMESHEET</span>
            <span class="dash-card-link">View consolidated team entries</span>
        </div>
    </a>
    {/if}
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
{/block}
