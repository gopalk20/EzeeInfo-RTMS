{extends file="layout/main.tpl"}
{block name="content"}
<h1>Products</h1>

{if empty($products)}
    <p>No products assigned. Contact your Product Lead or Manager to be added to a product.</p>
{else}
    <table class="data-table" style="width:100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background: #f0f0f0;">
                <th style="padding: 10px; text-align: left;">Name</th>
                <th style="padding: 10px; text-align: left;">Start Date</th>
                <th style="padding: 10px; text-align: left;">End Date</th>
            </tr>
        </thead>
        <tbody>
            {foreach $products as $p}
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;"><a href="/products/view/{$p.id}">{$p.name|escape}</a></td>
                <td style="padding: 10px;">{$p.start_date|escape}</td>
                <td style="padding: 10px;">{$p.end_date|escape}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>
{/if}
{/block}
