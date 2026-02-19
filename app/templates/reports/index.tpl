{extends file="layout/main.tpl"}
{block name="content"}
<h1>Reports</h1>
<p style="color:#666;">Select a date range and run a report. Finance and Manager can view reports. Manager sees costing.</p>

<div id="report-date-form" style="margin-bottom: 24px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
    <label style="display:block; font-weight:600; margin-bottom:12px;">Select date range</label>
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 16px;">
        <div>
            <label for="from" style="display:block; font-weight:500; margin-bottom:4px;">From Date</label>
            <input type="date" name="from" id="from" value="{$from|escape}" required style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
        </div>
        <div>
            <label for="to" style="display:block; font-weight:500; margin-bottom:4px;">To Date</label>
            <input type="date" name="to" id="to" value="{$to|escape}" required style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
        </div>
    </div>
    <p style="margin-top:12px; color:#666; font-size:0.9em;">Select your date range above, then click a report below. Default: current month.</p>
</div>

<ul style="list-style:none; padding:0;">
    <li style="padding:10px 0; border-bottom:1px solid #eee;">
        <a href="/reports/task-wise?from={$from|escape}&to={$to|escape}" class="btn">Task-wise Time Report</a>
        <span style="margin-left:12px; color:#666;">Time per task, rework %</span>
    </li>
    <li style="padding:10px 0; border-bottom:1px solid #eee;">
        <a href="/reports/employee-wise?from={$from|escape}&to={$to|escape}" class="btn">Employee-wise Time Report</a>
        <span style="margin-left:12px; color:#666;">Time per employee, costing (Manager)</span>
    </li>
    <li style="padding:10px 0; border-bottom:1px solid #eee;">
        <a href="/reports/performance?from={$from|escape}&to={$to|escape}" class="btn">Performance & Rework</a>
        <span style="margin-left:12px; color:#666;">Rework % by employee</span>
    </li>
</ul>
<script>
document.getElementById('from').addEventListener('change', function() {
    var from = this.value;
    var links = document.querySelectorAll('ul a.btn');
    links.forEach(function(a) {
        var href = new URL(a.href);
        href.searchParams.set('from', from);
        a.href = href.toString();
    });
});
document.getElementById('to').addEventListener('change', function() {
    var to = this.value;
    var links = document.querySelectorAll('ul a.btn');
    links.forEach(function(a) {
        var href = new URL(a.href);
        href.searchParams.set('to', to);
        a.href = href.toString();
    });
});
</script>
{/block}
