<?php
echo 'Sending Manufacturer Report '.date("Y-m-d H:i:s")." \n";
require_once dirname(__FILE__).'/vendor/autoload.php';
include_once(dirname(__FILE__).'/cron_include.php');
include_once(dirname(__FILE__).'/../../config/config.inc.php');

$adminProduction = new AdminProductionController();
$context->cookie->id_lang = 1;
$manufacturers = Manufacturer::getManufacturers();

$adminProduction->setOrderBy('shipping_date');
$adminProduction->setOrderWay('asc');

$weekStart = date("Y-m-d", strtotime("monday this week"));
$weekEnd = date("Y-m-d", strtotime("sunday this week"));

$mContainer = new ModuleContainer();
$container = $mContainer->getContainer();
$helperList = $container->get('helperList');
$productionOrderStates = $helperList->getProductionOrderStates();

foreach ($manufacturers as $manufacturer) {
    $aFilters = [];
    foreach ($productionOrderStates as $statusId) {
        $aFilters[] = [
            'key' => 'current_state',
            'operation' => '!==',
            'value' => $statusId,
        ];
    }
    
    $aFilters[] = [
        'key' => 'manufacturer',
        'operation' => '==',
        'value' => $manufacturer['id_manufacturer'],
    ];
    
    //the code was commented to include into report orders that have expired shipping date
    /*$aFilters[] = [
        'key' => 'shipping_date',
        'operation' => '>=',
        'value' => $weekStart,
    ];*/
    
    //the code was commented to include into report all upcoming orders
    /*$aFilters[] = [
        'key' => 'shipping_date',
        'operation' => '<=',
        'value' => $weekEnd,
    ];*/
    
    $adminProduction->setFilters($aFilters);
    $productionOrders = $adminProduction->getList($context->cookie->id_lang);
    if (is_array($productionOrders) && !empty($productionOrders)) {
        $orderIds = [];
        foreach ($productionOrders as $productionOrder) {
            if ($productionOrder['po_sent'] || $productionOrder['sa_sent']) {
                $orderIds[] = $productionOrder['id_order'];
            }
        }
        if (is_array($orderIds) && !empty($orderIds)) {
            $adminProduction->sendReports($orderIds);
        }
    }
}
?>