<?php

class ProductionHelperList extends AdminProductionController
{
    public $fields_list;
    public $container;
    
    public function __construct($container)
    {
        $this->context = Context::getContext();
        $this->container = $container;
    }
    
    public function getFieldsList()
    {
        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('ID'),
                'align' => 'left', 
                'class' => 'fixed-width-xs',
                'width' => 50,
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'align' => 'left',
                'class' => 'fixed-width-xs',
            ),
            'company' => array(
                'title' => $this->l('Company'),
                'align' => 'left',
                'class' => 'fixed-width-xs',
            ),
            'shipping_address' => array(
                'title' => $this->l('Shipping Address'),
                'align' => 'left',
                'class' => 'fixed-width-xs',
            ),
            'products' => array(
                'title' => $this->l('Products'),
                'align' => 'left',
                'class' => 'fixed-width-xs',
            ),
            'po_sent' => array(
                'title' => $this->l('PO was sent'),
                'align' => 'left',
                'class' => 'fixed-width-xs',
            ),
            'sa_sent' => array(
                'title' => $this->l('SA was sent'),
                'align' => 'left',
                'class' => 'fixed-width-xs',
            ),
            'shipping_date' => array(
                'title' => $this->l('Shipping date'),
                'align' => 'left',
                'class' => 'fixed-width-xs',
                'type' => 'date',
            ),
            'days_left' => array(
                'title' => $this->l('Days left'),
                'align' => 'left',
                'class' => 'fixed-width-xs',
            ),
            'manufacturer' => array(
                'title' => $this->l('Manufacturer'),
                'type' => 'select',
                'align' => 'left',
                'class' => 'fixed-width-xs',
                'list' => $this->getManufacturersList(),
                'filter_key' => 'manufacturer',
                'callback' => 'printManufacturer',
            ),
            'report_sent' => array(
                'title' => $this->l('Report was sent'),
                'align' => 'center',
                'active' => 'report_sent',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
            ),
            'current_state' => array(
                'title' => $this->l('Exlude Order Status'),
                'type' => 'exclude_selected',
                'color' => 'color',
                'align' => 'left',
                'class' => 'fixed-width-xs',
                'list' => $this->getOrderStatesList(),
                'filter_key' => 'current_state',
                'callback' => 'printOrderState',
            ),
        );
        return $this->fields_list;
    }
    
    public function getOrderStatesList()
    {
        $orderStatesIds = [];
        $orderStates = OrderState::getOrderStates((int)$this->context->language->id);
        foreach($orderStates as $orderState) {
            $orderStatesIds[$orderState['id_order_state']] = $orderState['name'];
        }
        return $orderStatesIds;
    }
    
    public function getManufacturersList()
    {
        $manufacturersIds = [];
        foreach(Manufacturer::getManufacturers() as $aManuf) {
            $manufacturersIds[$aManuf['id_manufacturer']] = $aManuf['name'];
        }
        return $manufacturersIds;
    }
    
    public function sortProductionOrders($productionOrders, $orderBy = 'id_order', $orderWay = 'DESC')
    {
        $volume = [];
        foreach ($productionOrders as $key => $row) {
            $volume[$key]  = $row[$orderBy];
            if ($orderBy == 'po_sent' || $orderBy == 'sa_sent' || $orderBy == 'shipping_date') {
                $volume[$key]  = new DateTime($row[$orderBy]);
                if ($row[$orderBy] == '' || $row[$orderBy] == '--') {
                    $volume[$key]  = new DateTime('0000-00-00');
                }
            }
        }
        
        if ($orderWay === 'ASC') {
            $sort_order = SORT_ASC;
        } elseif ($orderWay === 'DESC') {
            $sort_order = SORT_DESC;
        } else {
            $sort_order = SORT_ASC;
        }
        
        array_multisort($volume, $sort_order, $productionOrders);
        return $productionOrders;
    }
    
    public function filteringProductionOrders($productionOrders = [], $filters = []) {
        if (empty($filters)) {
            return $productionOrders;
        } 
        foreach ($filters as $filter) {
            $productionOrders = $this->aplyFiter($productionOrders, $filter);
        }
        return $productionOrders;
    }
    
    public function aplyFiter($productionOrders, $filter)
    {
        $resultArray = [];
        foreach ($productionOrders as $productionOrder) {
            if ($this->checkProductionOrder($productionOrder, $filter)) {
                $resultArray[] = $productionOrder;
            }
        }
        return $resultArray;
    }
    
    public function checkProductionOrder($productionOrder, $filter)
    {
        if ($this->fields_list[$filter['key']]['type'] == 'date') {
            return $this->checkDate($productionOrder, $filter);
        } else {
            return $this->checkValue($productionOrder, $filter);
        }
        
    }
    
    public function checkValue($productionOrder, $filter)
    {
        if (empty($filter['key']) ||
            empty($filter['operation']) ||
            empty($filter['value']) || 
            empty($productionOrder[$filter['key']]) ||
            !is_int($productionOrder[$filter['key']])
        ) {
            return false;
        }
        
        $condition = $productionOrder[$filter['key']].' '.$filter['operation'].' '.$filter['value'];
        $checking = eval("return $condition;");
        
        if ($checking) {
           return true;
        }
        return false;
    }
    
    public function checkDate($productionOrder, $filter)
    {
        $key = $productionOrder[$filter['key']];
        $value = $filter['value'];
        $operation = $filter['operation'];
        
        if (empty($key)) {
            return false;
        }
        
        $condition = "new DateTime('$key') $operation new DateTime('$value')";
        $checking = eval("return $condition;");
        if ($checking) {
           return true;
        }
        return false;
    }
    
    public function getProductionOrders($ordersIds = [])
    {
        $emptyRow = [
            'id_order' => '',
            'reference' => '',
            'customer' => '',
            'company' => '',
            'shipping_address' => '',
            'products' => '',
            'po_sent' => '',
            'sa_sent' => '',
            'shipping_date' => '',
            'days_left' => '',
            'manufacturer' => '',
            'report_sent' => '',
        ];
        
        $productionOrders = [];
        
        if (!is_array($ordersIds) || empty($ordersIds)) {
            return [0 => $emptyRow];
        }
        
        foreach ($ordersIds as $orderId) {
            
            if (empty($orderId) || !is_int($orderId)) {
                $productionOrders[] = $emptyRow;
                continue;
            }
            
            $this->initProductionOrder((int) $orderId);
            $row = $this->productionOrder->getRow();
            
            if (!is_array($row) || empty($row) || empty($row['products'])) {
                $productionOrders[] = $emptyRow;
                continue;
            }
            
            $productionOrders[] = $row;
        }
        return $productionOrders;
    }
    
    public function getFilters($filtersFamily, $prefix, $list_id)
    {
        $filters = [];
        foreach ($filtersFamily as $key => $value) {
            $string = $prefix.$list_id.'Filter_';
            $length = 7 + Tools::strlen($prefix.$list_id);
            
            if ($value != null && !strncmp($key, $string, $length)) {
                $filter = $this->extratctFilter($key, $value, $length);
            }
            
            if (!empty($filter) && is_array($filter)) {
                foreach ($filter as $item) {
                    $filters[] = $item;
                }
            }
            
        }
        return $filters;
    }
    
    public function extratctFilter($key, $value, $length)
    {
        // if $value is serialized - unserialize it, otherwise use unchanged $value
        $data = @unserialize($value);
        // 'b:0' is serialize(false), so we have to access to this false value
        if ($value === 'b:0;' || $data !== false) {
            $value = $data;
        }
        
        // cut off prefix
        $key = Tools::substr($key, $length);
        
        $resultFilter = $this->getFilter($key, $value);
        return $resultFilter;
    }
    
    public function getFilter($key, $value)
    {
        $filter = [];
        if (is_array($value)) {
            if ($key == 'shipping_date') {
                $filter = $this->getShippingDateFilter($key, $value);
            } elseif ($key == 'current_state') {
                // this case is used when we want exclude some states, it is used in prepared filters 
                $filter = $this->getNotStateFilter($key, $value);
            }
        } else {
            $filter[] = [
                'key' => $key,
                'operation' => '==',
                'value' => $value
            ];
        }
        return $filter;
    }
    
    public function getNotStateFilter($key, $values)
    {
        $filter = [];
        if (is_array($values)) {
            foreach ($values as $value) {
                if (!empty($value)) {
                    $filter[] = [
                        'key' => $key,
                        'operation' => '!==',
                        'value' => $value,
                    ];
                }
            }
        }
        return $filter;
    }
    
    public function getShippingDateFilter($key, $values)
    {
        $filter = [];
         if (isset($values[0]) && !empty($values[0])) {
            $filter[] = [
                'key' => $key,
                'operation' => '>=',
                'value' => $values[0],
            ];
        }

        if (isset($values[1]) && !empty($values[1])) {
            $filter[] = [
                'key' => $key,
                'operation' => '<=',
                'value' => $values[1],
            ];
        }
        return $filter;
    }
    
    public function nl2brForFileds($array = [], $fields = [])
    {
        array_walk_recursive($array, function (&$value, $key)  use ($fields) {
            if (in_array($key, $fields)) {
                $value = nl2br($value);
            }
        });
        return $array;
    }
    
    public function getProductionOrderStates()
    {
        $orderStates = Configuration::get('PRODUCTION_ORDER_STATES');
        $orderStates = @unserialize($orderStates);
        if ($orderStates === false && $orderStates !== 'b:0;') {
            $orderStates = array();
        }
        return $orderStates;
    }
}