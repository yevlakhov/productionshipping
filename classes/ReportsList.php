<?php

class ReportsList
{
    public $container;
    public $helperList;
    
    public function __construct($container = null)
    {
        $this->container = $container;
        $this->helperList = $this->container->get('helperList');
    }
    
    /**
     * @param array $ordersIds
     * @return array
     */
    public function getManufacturersReports($ordersIds = [])
    {
        $manufacturersOrdersIds = $this->getOrdersIdsByManufacturers($ordersIds);
        $manufacturersReports = [];
        foreach ($manufacturersOrdersIds as $idManuf => $idsOrders) {
            $this->container->setParameter('manufacturerId', $idManuf);
            $this->container->setParameter('ordersIds', $idsOrders);
            $manufacturerReport = $this->container->get('manufacturerReport');
            $manufacturersReports[$idManuf] = $manufacturerReport;
        }
        return $manufacturersReports;
    }
    
    public function getOrdersIdsByManufacturers($ordersIds = [])
    {
        $productionOrders = $this->helperList->getProductionOrders($ordersIds);
        $manufacturersOrdersIds = [];
        foreach ($productionOrders as $productionOrder) {
            $manufacturersOrdersIds[$productionOrder['manufacturer']][] = $productionOrder['id_order'];
        }
        return $manufacturersOrdersIds;
    }
    
}