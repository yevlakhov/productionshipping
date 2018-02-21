<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

class ModuleContainer
{
    
    public function getContainer()
    {
        $container = new ContainerBuilder();
        
        $container->register('helperList', 'ProductionHelperList')
            ->addArgument($container);
        
        $container->register('apcontroller', 'AdminProductionController');
        
        $container->register('productionshipping', 'Productionshipping');
        
        $productionOrderDefinition = new Definition('ProductionOrder', ['idOrder' => '%apcontroller.idOrder%', 'testEnv' => false, 'container' => $container]);
        $productionOrderDefinition->setShared(false);
        $container->setDefinition('productionOrder', $productionOrderDefinition);
        
        $manufacturerReportDefinition = new Definition('ManufacturerReport', ['ordersIds' => '%ordersIds%', 'manufacturerId' => '%manufacturerId%', 'container' => $container]);
        $manufacturerReportDefinition->setShared(false);
        $container->setDefinition('manufacturerReport', $manufacturerReportDefinition);
        
        $container->register('reportsList', 'ReportsList')
            ->addArgument($container);
        
        $manufacturerDefinition = new Definition('Manufacturer', ['manufacturerId' => '%manufacturerId%']);
        $manufacturerDefinition->setShared(false);
        $container->setDefinition('manufacturer', $manufacturerDefinition);
        
        $orderDefinition = new Definition('InheritedOrder', ['orderId' => '%orderId%']);
        $orderDefinition->setShared(false);
        $container->setDefinition('order', $orderDefinition);
        
        $productionOrderState = new Definition('ProductionOrderState', ['idOrderState' => '%idOrderState%']);
        $productionOrderState->setShared(false);
        $container->setDefinition('productionOrderState', $productionOrderState);
        
        $paginationDefinition = new Definition('Pagination', ['tableName' => '%tableName%']);
        $paginationDefinition->setShared(false);
        $container->setDefinition('pagination', $paginationDefinition);
        
        return $container;
    }

}