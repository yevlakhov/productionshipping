
<style>
    
.adminproduction .bootstrap .nav-tabs li form a {
    font-size: 1.1em;
    font-family: "Ubuntu Condensed",Helvetica,Arial,sans-serif;
    text-transform: uppercase;
    font-weight: 300;
}
.adminproduction .bootstrap .nav-tabs>li>form>a {
    margin-right: 2px;
    line-height: 1.42857;
    border: 1px solid transparent;
    border-radius: 3px 3px 0 0;
}
.adminproduction .bootstrap .nav>li>form>a, .bootstrap #header_notifs_icon_wrapper>li>a,
.adminproduction .bootstrap #header_employee_box>li>a,
.adminproduction .bootstrap #header_quick>li>a {
    position: relative;
    display: block;
    padding: 10px 15px;
}
    
.adminproduction .bootstrap .nav-tabs>li>form>a:hover {
    border-color: #eee #eee #ddd;
    text-decoration: none;
    background-color: #eee;
}

.adminproduction #tab_{$current_tab} a{
    background-color: #fff;
    color: #555;
    background-color: #eee;
    border: 1px solid #ddd;
    border-bottom-color: transparent;
    cursor: default;
}
    
</style>

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li id="tab_0">
        <form id="form_0" action="{$link->getAdminLink('AdminProduction')|escape:'html':'UTF-8'}&ordersOrderby=id_order&ordersOrderway=DESC&current_tab=0" method="post">
            <a href="#" onclick="document.getElementById('form_0').submit();">{l s='All Orders' mod='productionshipping'}</a>
            <input type="hidden" name="ordersFilter_manufacturer" value="" />
            <input type="hidden" name="submitFilterorders" value="0" />
            <input type="hidden" name="ordersFilter_current_state" value="" />
            
            {*<input type="hidden" name="ordersFilter_shipping_date" value="" />*}
        </form>
    </li>
{foreach $manufacturers as $manufacturer}
    <li id="tab_{$manufacturer.id_manufacturer|escape:'html':'UTF-8'}">
        <form id="form_{$manufacturer.id_manufacturer|escape:'html':'UTF-8'}" action="{$link->getAdminLink('AdminProduction')|escape:'html':'UTF-8'}&ordersOrderby=shipping_date&ordersOrderway=asc&current_tab={$manufacturer.id_manufacturer|escape:'html':'UTF-8'}" method="post">
            <a href="#" onclick="document.getElementById('form_{$manufacturer.id_manufacturer|escape:'html':'UTF-8'}').submit();" {*role="tab" data-toggle="tab"*}>{$manufacturer.name|escape:'html':'UTF-8'}</a>
            <input type="hidden" name="ordersFilter_manufacturer" value="{$manufacturer.id_manufacturer|escape:'html':'UTF-8'}" />
            <input type="hidden" name="submitFilterorders" value="1" />
            {foreach $production_order_states as $statusId}
                <input type="hidden" name="ordersFilter_current_state[]" value="{$statusId}" />
            {/foreach}
            
            {*<input type="hidden" name="ordersFilter_shipping_date[]" value="{$weekStart}" />
            <input type="hidden" name="ordersFilter_shipping_date[]" value="{$weekEnd}" />*}
        </form>
    </li>
{/foreach}
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div class="tab-pane active">{include file='./orders.tpl'}</div>
</div>