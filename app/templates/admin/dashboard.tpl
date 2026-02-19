{extends file="layout/main.tpl"}
{block name="content"}
<div class="page-header">
    <h1 class="page-title">Admin Dashboard</h1>
    <p class="page-subtitle">Overview for {$from|escape} – {$to|escape}</p>
</div>

<div class="dashboard-grid">
    <div class="dashboard-widget">
        <h3 class="widget-title">Overall Hours</h3>
        <div class="donut-container">
            {assign var="billable" value=$overall_hours.billable|default:0}
            {assign var="non_billable" value=$overall_hours.non_billable|default:0}
            {assign var="total_hrs" value=$billable+$non_billable}
            {if $total_hrs > 0}
                {assign var="billable_pct" value=($billable/$total_hrs)*100}
                {assign var="non_billable_pct" value=($non_billable/$total_hrs)*100}
            {else}
                {assign var="billable_pct" value=0}
                {assign var="non_billable_pct" value=0}
            {/if}
            <div class="donut-chart" style="--billable: {$billable_pct}; --non-billable: {$non_billable_pct};">
                <div class="donut-value">{$total_hrs|string_format:"%.1f"}h</div>
            </div>
            <div class="donut-legend">
                <span class="legend-item"><span class="legend-dot billable"></span> Billable</span>
                <span class="legend-item"><span class="legend-dot non-billable"></span> Non-billable</span>
            </div>
        </div>
    </div>

    <div class="dashboard-widget">
        <h3 class="widget-title">Work Hours Summary (Monthly)</h3>
        <div class="bar-chart-container">
            {assign var="max_val" value=0}
            {foreach $monthly_hours as $m => $data}
                {assign var="sum" value=$data.billable+$data.non_billable}
                {if $sum > $max_val}{assign var="max_val" value=$sum}{/if}
            {/foreach}
            {if $max_val < 1}{assign var="max_val" value=1}{/if}
            {foreach $monthly_hours as $m => $data}
                <div class="bar-row">
                    <span class="bar-label">{$m|escape}</span>
                    <div class="bar-track">
                        <div class="bar-seg billable" style="width: {($data.billable/$max_val)*100}%;" title="Billable: {$data.billable|string_format:"%.1f"}h"></div>
                        <div class="bar-seg non-billable" style="width: {($data.non_billable/$max_val)*100}%;" title="Non-billable: {$data.non_billable|string_format:"%.1f"}h"></div>
                    </div>
                    <span class="bar-value">{$data.billable+$data.non_billable|string_format:"%.0f"}</span>
                </div>
            {/foreach}
            {if empty($monthly_hours)}
                <p class="text-muted">No hours recorded this period.</p>
            {/if}
        </div>
    </div>

    <div class="dashboard-widget">
        <h3 class="widget-title">Resource Allocation (by Project)</h3>
        <div class="allocation-container">
            {assign var="prod_total" value=0}
            {foreach $hours_by_product as $p}{assign var="prod_total" value=$prod_total+$p.total}{/foreach}
            {if $prod_total > 0}
                <div class="allocation-list">
                    {foreach $hours_by_product as $idx => $p}
                        {assign var="pct" value=($p.total/$prod_total)*100}
                        <div class="allocation-item">
                            <span class="alloc-color alloc-color-{$idx%8}"></span>
                            <span class="alloc-name">{$p.name|escape}</span>
                            <span class="alloc-hours">{$p.total|string_format:"%.1f"}h ({$pct|string_format:"%.0f"}%)</span>
                        </div>
                    {/foreach}
                </div>
            {else}
                <p class="text-muted">No hours by project this period.</p>
            {/if}
        </div>
    </div>

    <div class="dashboard-widget">
        <h3 class="widget-title">Pending Approvers</h3>
        <div class="approvers-list">
            {if !empty($pending_approvers)}
                {foreach $pending_approvers as $a}
                <a href="/approval" class="approver-item">
                    <div class="approver-avatar">{$a.first_name|substr:0:1|upper}{$a.last_name|substr:0:1|upper}</div>
                    <div class="approver-info">
                        <span class="approver-name">{$a.first_name|escape} {$a.last_name|escape}</span>
                        <span class="approver-role">{$a.role_name|escape}</span>
                    </div>
                    <span class="approver-count">{$a.timesheet_count} Timesheet{if $a.timesheet_count != 1}s{/if}</span>
                </a>
                {/foreach}
            {else}
                <p class="text-muted">No pending approvals.</p>
            {/if}
        </div>
    </div>

    <div class="dashboard-widget financial-summary">
        <h3 class="widget-title">Financial Summary</h3>
        <div class="financial-cards">
            <div class="fin-card">
                <span class="fin-label">Total Revenue Generated</span>
                <span class="fin-value">USD {$total_revenue|string_format:"%.0f"}</span>
            </div>
            <div class="fin-card">
                <span class="fin-label">Pending hours to be invoiced</span>
                <span class="fin-value">{$pending_hours|string_format:"%.0f"} hours</span>
            </div>
            <div class="fin-card">
                <span class="fin-label">Pending Payments</span>
                <span class="fin-value">{$pending_invoices} Invoices {if $pending_payments > 0}(USD {$pending_payments|string_format:"%.0f"}){/if}</span>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
}
.dashboard-widget {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    padding: 20px;
}
.widget-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 8px;
}
.donut-container { display: flex; flex-direction: column; align-items: center; gap: 12px; }
.donut-chart {
    width: 140px; height: 140px;
    border-radius: 50%;
    background: conic-gradient(
        #10b981 0deg calc(var(--billable, 0) * 3.6deg),
        #f87171 calc(var(--billable, 0) * 3.6deg) 360deg
    );
    display: flex; align-items: center; justify-content: center;
}
.donut-value {
    width: 90px; height: 90px;
    background: white; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; font-weight: 700; color: #1e293b;
}
.donut-legend { font-size: 0.85rem; color: #64748b; }
.legend-item { margin-right: 12px; }
.legend-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 4px; vertical-align: middle; }
.legend-dot.billable { background: #10b981; }
.legend-dot.non-billable { background: #f87171; }
.bar-chart-container { font-size: 0.9rem; }
.bar-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.bar-label { min-width: 60px; color: #64748b; }
.bar-track {
    flex: 1;
    display: flex;
    height: 22px;
    border-radius: 4px;
    overflow: hidden;
    background: #f1f5f9;
}
.bar-seg { min-width: 2px; }
.bar-seg.billable { background: #f59e0b; }
.bar-seg.non-billable { background: #3b82f6; }
.bar-value { min-width: 36px; text-align: right; font-weight: 500; color: #374151; }
.allocation-list { max-height: 200px; overflow-y: auto; }
.allocation-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
.alloc-color { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.alloc-color-0 { background: #3b82f6; }
.alloc-color-1 { background: #10b981; }
.alloc-color-2 { background: #f59e0b; }
.alloc-color-3 { background: #ef4444; }
.alloc-color-4 { background: #8b5cf6; }
.alloc-color-5 { background: #ec4899; }
.alloc-color-6 { background: #06b6d4; }
.alloc-color-7 { background: #84cc16; }
.alloc-name { flex: 1; font-size: 0.9rem; }
.alloc-hours { font-size: 0.85rem; color: #64748b; }
.approvers-list { max-height: 220px; overflow-y: auto; }
.approver-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 0; border-bottom: 1px solid #f1f5f9;
    text-decoration: none; color: inherit;
}
.approver-item:hover { background: #f8fafc; }
.approver-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: #6f42c1; color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; font-weight: 600; flex-shrink: 0;
}
.approver-info { flex: 1; }
.approver-name { display: block; font-weight: 500; color: #1e293b; }
.approver-role { font-size: 0.8rem; color: #64748b; }
.approver-count { font-size: 0.9rem; font-weight: 500; color: #6f42c1; }
.financial-summary .financial-cards { display: flex; flex-direction: column; gap: 16px; }
.fin-card {
    padding: 16px; background: #f8fafc; border-radius: 6px;
    border-left: 4px solid #6f42c1;
}
.fin-label { display: block; font-size: 0.8rem; color: #64748b; margin-bottom: 4px; }
.fin-value { font-size: 1.25rem; font-weight: 600; color: #1e293b; }
.text-muted { color: #94a3b8; font-size: 0.9rem; }
</style>
{/block}
