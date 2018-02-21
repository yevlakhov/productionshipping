<?php

class ManufacturerReport extends ObjectModel
{
    public $container;
    public $ordersIds;
    public $reportFile;
    public $manufacturer;
    public $mailVars;
    
    public function __construct($ordersIds = [], $manufacturerId = '', $container = null)
    {
        $this->context = Context::getContext();
        $this->ordersIds = $ordersIds;
        $this->container = $container;
        $this->container->setParameter('manufacturerId', $manufacturerId);
        $this->manufacturer = $this->container->get('manufacturer');
        $this->helperList = $this->container->get('helperList');
        //$this->reportFile = getReportFile();
        $this->mailVars = $this->getMailVars();
    }
    
    public function getReportFile()
    {
        //return new PDF(, , )
    }
    
    public function getMailVars()
    {
        $mailVars = [
            '{shop_name}' => '',
            '{shop_logo}' => '',
            '{shop_url}' => '',
            '{manufName}' => $this->manufacturer->name,
            '{report}' => $this->getReportTable(),
        ];
        
        return $mailVars;
    }
    
    public function fixShippingDateFormat($productionOrders) {
        foreach ($productionOrders as $key => $productionOrder) {
            if (!empty($productionOrder['shipping_date'])) {
                $shippingDate = new DateTime($productionOrder['shipping_date']);
                $shippingDate = $shippingDate->format('n/j/Y');
                $productionOrders[$key]['shipping_date'] = $shippingDate;
            }
        }
        return $productionOrders;
    }
    
    public function getReportTable()
    {
        $productionOrders = $this->helperList->getProductionOrders($this->ordersIds);
        $productionOrders = $this->helperList->sortProductionOrders($productionOrders, 'shipping_date', 'ASC');
        $productionOrders = $this->helperList->nl2brForFileds($productionOrders, ['products']);
        $productionOrders = $this->fixShippingDateFormat($productionOrders);
        $this->context->smarty->assign([
            'productionOrders' => $productionOrders,
        ]);
        $output = $this->context->smarty->fetch(_PS_MODULE_DIR_.'productionshipping/views/templates/admin/pdf/manuf-report.tpl');
        return $output;
    }
    
    public function sendReport()
    {
        $adminEmail = Configuration::get('ADMIN_EMAIL');
        $manufEmail =  $this->manufacturer->email;
        $emails = [];
        if (Validate::isEmail($manufEmail)) {
            $emails[] = $manufEmail;
        }
        
        if (Validate::isEmail($adminEmail)) {
            $emails[] = $adminEmail;
        }
        
        $mailVars = $this->mailVars;
        $ordersIds = $this->ordersIds;
        $name = $this->manufacturer->name;
        
        $pdf = new PDFProduction($this, 'ManufacturerReport', $this->context->smarty, 'L');
        $file_attachment['content'] = $pdf->render(false);
        $file_attachment['name'] = 'report.pdf';
        $file_attachment['mime'] = 'application/pdf';
        
        $subject = 'Report';
        
        if (empty($emails) || empty($mailVars) || empty($ordersIds)) {
            return;
        }
        
        if (Mail::Send(
            (int)$this->context->cookie->id_lang,
            'manufacturer-report',
            $subject,
            $mailVars,
            $emails,
            $name,
            null,
            null,
            $file_attachment,
            null,
            //_PS_MODULE_DIR_.Configuration::get('PRODUCTIONSHIPPING').'/mails/',
            //@todo: change hardcode
            _PS_MODULE_DIR_.'productionshipping/mails/',
            true,
            null
        )) {
            $this->setSentStatuses($ordersIds);
        }
    }
    
    public function setSentStatuses($ordersIds = [])
    {
        if (empty($ordersIds) || !is_array($ordersIds)) {
            return;
        }
        
        foreach ($ordersIds as $orderId) {
            $productionOrder = $this->getProductionOrder((int) $orderId);
            $productionOrder->setStatus(true);
        }
    }
    
    public function getProductionOrder($orderId = '')
    {
        if (!empty($orderId) && is_int($orderId)) {
            $this->container->setParameter('apcontroller.idOrder', $orderId);
            $productionOrder = $this->container->get('productionOrder');
            return $productionOrder;
        }
        return null;
    }
}