<style type="text/css">
   
table {
    border-collapse: collapse;
    margin: auto;
    font-size: 20px;
}
    
tr {
    display: table-row;
    vertical-align: inherit;
    border-color: inherit;
}

td{
    padding: 3px;
    border: 0.3px solid black;
}
    
th{
    border: 0.3px solid black;
    text-align: center;
}
    
</style>
<br><br>
<table style="width: 100%;" cellpadding="3">
    <tr>
        <th width="13%">{l s='Document Reference' mod='productionshipping'}</th>
        <th width="10%">{l s='Customer' mod='productionshipping'}</th>
        <th width="10%">{l s='Company' mod='productionshipping'}</th>
        <th width="13%">{l s='Shipping Address' mod='productionshipping'}</th>
        <th width="20%">{l s='Products' mod='productionshipping'}</th>
        <th width="10%">{l s='Purchase Order Was Sent' mod='productionshipping'}</th>
        <th width="10%">{l s='Shipping Authorization Was Sent' mod='productionshipping'}</th>
        <th width="10%">{l s='Shipping Date' mod='productionshipping'}</th>
        <th width="4%">{l s='Days Left' mod='productionshipping'}</th>
    </tr>
    
    {foreach $productionOrders as $productionOrder}
        <tr>
            <td>{$productionOrder['reference']}</td>
            <td>{$productionOrder['customer']}</td>
            <td>{$productionOrder['company']}</td>
            <td>{$productionOrder['shipping_address']}</td>
            <td>{$productionOrder['products']}</td>
            <td>{$productionOrder['po_sent']}</td>
            <td>{$productionOrder['sa_sent']}</td>
            <td>{$productionOrder['shipping_date']}</td>
            <td>{$productionOrder['days_left']}</td>
        </tr>
    {/foreach}
</table>