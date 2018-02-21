<?php

class ProductionOrder extends ObjectModel
{
    
    public $ordersList = [];
    public $testEnv;
    public $order;
    public $address;
    public $poSentDate;
    public $saSentDate;
    public $poShippingPeriod;
    public $saShippingPeriod;
    public $customer;
    public $customerName;
    public $companyName;
    public $shippingAddress;
    public $productsRefs;
    public $shippingDate;
    public $daysLeft;
    public $currentDate;
    public $manufacturers;
    public $manufacturer;
    public $orderState;
    public $container;
    public $documentReference;
    public $sufix;
    
    public function __construct($idOrder, $testEnv = false, $container)
    {
        $this->context = Context::getContext();
        $this->container = $container;
        $this->testEnv = $testEnv;
        $this->initOrder($idOrder);
        $this->initDates($idOrder);
        $this->initCustomer();
        $this->customerName = $this->getCustomerName();
        $this->companyName = $this->getCompanyName();
        $this->shippingAddress = $this->getShippingAddress();
        $this->productsRefs = $this->getProducts();
        $this->sufix = $this->getSufix();
        $this->shippingDate = $this->getShippingDate();
        $this->currentDate = $this->getCurrentDate();
        $this->daysLeft = $this->getDaysLeft();
        $this->manufacturers = $this->getManufacturers();
        $this->manufacturer = $this->getManufacturer();
        $this->orderState = $this->getCurrentOrderState();
        $this->documentReference = $this->getDocumentReference();
    }
    
    public function initOrder($idOrder = null)
    {
        if ($this->testEnv === true) {
            return;
        }
        
        if ($idOrder) {
            $this->container->setParameter('orderId', $idOrder);
            $this->order = $this->container->get('order');
        }
        
    }
    
    public function initCustomer()
    {
        if ($this->testEnv === true) {
            return;
        }
        
        if (!empty($this->order)) {
            $this->customer = $this->order->getCustomer();
        }
    }
    
    
    public function initAddress($idAddress = null)
    {
        if ($this->testEnv === true) {
            return;
        }
        
        if ($idAddress) {
            $this->address = new Address($idAddress);
        }
        
    }
    
    public function initDates($idOrder)
    {
        
        if ($this->testEnv === true) {
            return;
        }
        
        $sql = '
            SELECT pp.date_purchase, pp.date_shipauth, pg.shipping_period, pg.shipping_period_sa
            FROM '._DB_PREFIX_.'orders o
            LEFT JOIN '._DB_PREFIX_.'module_purchase_generateform pg ON (o.id_order = pg.id_order)
            LEFT JOIN '._DB_PREFIX_.'module_purchase_pdf pp ON (o.id_order = pp.id_order)
            WHERE o.id_order = "'.pSQL($idOrder).'"
        ';
        $result = DB::getInstance()->executeS($sql);
        $this->poSentDate = $result[0]['date_purchase'];
        $this->saSentDate = $result[0]['date_shipauth'];
        $this->poShippingPeriod = $result[0]['shipping_period'];
        $this->saShippingPeriod = $result[0]['shipping_period_sa'];
    }
    
    public function getCustomerName()
    {
        if (!empty($this->customer->firstname) && !empty($this->customer->lastname)) {
            return $this->customer->firstname.' '.$this->customer->lastname;
        }
        return '';
    }
    
    public function getCompanyName()
    {
        if (!empty($this->customer->company)) {
            return $this->customer->company;
        }
        return '';
    }
    
    public function getShippingAddress()
    {
        if (empty($this->order->id_address_delivery)) {
            return '';
        }
        $this->initAddress($this->order->id_address_delivery);
        $address = '';
        
        if (!empty($this->address->address1)) {
            $address .= $this->address->address1 .' ';
        }
        
        if (!empty($this->address->postcode)) {
            $address .= $this->address->postcode .' ';
        }
        
        if (!empty($this->address->city)) {
            $address .= $this->address->city .' ';
        }
        
        if (!empty($this->address->state)) {
            $address .= $this->address->state .' ';
        }
        
        if (!empty($this->address->country)) {
            $address .= $this->address->country .' ';
        }
        
        return $address;
    }
    
    public function getProducts()
    {
        $prods = $this->order->getProducts();
        if (!is_array($prods) || empty($prods)) {
            return '';
        }
        $products = '';
        foreach ($prods as $product) {
            if (empty($product) || !is_string($product['product_reference']) || empty($product['product_reference'])) {
                continue;
            }
            $products .= $product['product_reference']." - ".$product['product_quantity']." pcs, \n";
        }
        return $products;
    }
    
    public function getManufacturers()
    {
        $prods = $this->order->getProducts();
        if (!is_array($prods) || empty($prods)) {
            return '';
        }
        $manufacturers = [];
        
        foreach ($prods as $product) {
            if (empty($product) || !is_string($product['id_manufacturer']) || empty($product['id_manufacturer'])) {
                continue;
            }
            $oManufacturer = new Manufacturer($product['id_manufacturer']);
            $manufacturers[$oManufacturer->id] = $oManufacturer->name;
        }
        
        return $manufacturers;
    }
    
    public function getManufacturer()
    {
        $manufacturers = $this->manufacturers;
        return key($manufacturers);
    }
    
    public function getSentDate()
    {
        if (!empty($this->poSentDate)) {
            $date = $this->poSentDate;
        } elseif (!empty($this->saSentDate)) {
            $date = $this->saSentDate;
        } else {
            return new DateTime('00/00/0000');
        }
        
        if (!is_string($date) ) {
            return new DateTime('00/00/0000');
        }
        
        try {
            $sentDate = new DateTime($date);
        } catch (Exception $e) {
            return new DateTime('00/00/0000');
        }
        
        return $sentDate;
    }
    
    public function getPoShippingPeriod()
    {
        if ((is_string($this->poShippingPeriod) || is_int($this->poShippingPeriod)) && !empty($this->poShippingPeriod)) {
            return (int)$this->poShippingPeriod;
        } else {
            return 0;
        }
    }
    
    public function getSaShippingPeriod()
    {
        if ((is_string($this->saShippingPeriod) || is_int($this->saShippingPeriod)) && !empty($this->saShippingPeriod)) {
            return (int)$this->saShippingPeriod;
        } else {
            return 0;
        }
    }
        
    public function getSufix()
    {
        if (is_string($this->poSentDate) && !empty($this->poSentDate)) {
            return 'PO';
        } elseif (is_string($this->saSentDate) && !empty($this->saSentDate)) {
            return 'SA';
        } else {
            return null;
        }
    }
    
    public function getShippingPeriod()
    {
        $poShippingPeriod = $this->getPoShippingPeriod();
        $saShippingPeriod = $this->getSaShippingPeriod();
        
        if (!is_int($poShippingPeriod) || empty($poShippingPeriod)) {
            $poShippingPeriod = 0;
        }
        
        if (!is_int($saShippingPeriod) || empty($saShippingPeriod)) {
            $saShippingPeriod = 0;
        }
        
        if ($this->sufix == 'PO') {
            $shippingPeriod = $poShippingPeriod;
        } elseif ($this->sufix == 'SA') {
            $shippingPeriod = $saShippingPeriod;
        } else {
            return 0;
        }
        return (int)$shippingPeriod;
    }
    
    public function getShippingDate($format = 'Y-m-d')
    {
        $sentDate = $this->getSentDate();
        $shippingPeriod = $this->getShippingPeriod();
        
        if (!($sentDate instanceof DateTime) || $sentDate == new DateTime('00/00/0000')) {
            return '';
        }
        
        if (!is_int($shippingPeriod) || empty($shippingPeriod)) {
            return '';
        }
        
        try {
            $shippingPeriod = new DateInterval('P'.$shippingPeriod.'D');
        } catch (Exception $e) {
            return 'Wrong interval';
        }
        $shippingDate = $sentDate->add($shippingPeriod);
        return $shippingDate->format($format);
        
    }

    /**
     * return current DateTime with h:m:s (00:00:00)
     * 
     * @return DateTime
     */
    public function getCurrentDate()
    {
        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');
        $currentDate = new DateTime($currentDate);
        return $currentDate;
    }
    
    public function getDaysLeft()
    {
        //reset h:m:s
        $currentDate = $this->currentDate->format('Y-m-d');
        $this->currentDate = new DateTime($currentDate);
        
        if (empty($this->shippingDate)) {
            return 0;
        }
        
        try {
            $shippingDate = new DateTime($this->shippingDate);
        } catch (Exception $e) {
            return 0;
        }
        
        $sign = '';
        if ($shippingDate < $this->currentDate) {
            $sign = '-';
        }
        
        $interval = $shippingDate->diff($this->currentDate);
        return $sign.$interval->days;
    }
    
    public function getCurrentOrderState()
    {
        $currentOrderState = $this->order->getCurrentStateFull((int)$this->context->language->id);
        $this->container->setParameter('idOrderState', $currentOrderState['id_order_state']);
        $orderState = $this->container->get('productionOrderState');
        return $orderState;
    }
    
    public function getDocumentReference()
    {
        return $this->order->getFilename($this->sufix);
    }
    
    public function getRow()
    {
        $row = [];
        $row['id_order'] = $this->order->id;
        $row['reference'] = $this->documentReference;
        $row['customer'] = $this->customerName;
        $row['company'] = $this->companyName;
        $row['shipping_address'] = $this->shippingAddress;
        $row['products'] = $this->productsRefs;
        $row['po_sent'] = $this->poSentDate;
        $row['sa_sent'] = $this->saSentDate;
        $row['shipping_date'] = $this->shippingDate;
        $row['days_left'] = $this->daysLeft;
        $row['manufacturer'] = $this->manufacturer;
        $row['report_sent'] = (int) $this->order->report_sent;
        $row['current_state'] = (int) $this->orderState->idOrderState;
        $row['color'] = $this->orderState->color;
        return $row;
    }
    
    public function setStatus($status)
    {
        if (!isset($status) || !is_bool($status)) {
            return false;
        }
        
        $status = (int) $status;
        
        if ($status !== (int)$this->order->report_sent) {
            $result = DB::getInstance()->update('orders', ['report_sent' => $status], 'id_order = '.$this->order->id);
            return $result;
        }
        return false;
    }
    
    /*public function getOrdersByManufacturer()
    {
        
    }*/
}