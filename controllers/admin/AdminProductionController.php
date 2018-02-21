<?php

require_once dirname(__FILE__).'/../../vendor/autoload.php';

class AdminProductionController extends ModuleAdminController
{    
    
    public $fields_list;
    public $_list;
    public $productionOrder;
    public $filters = [];
    public $manufacturersReportsList;
    
    public function __construct($container = null)
    {
        if (($container instanceof ModuleContainer) && !empty($container)) {
            $this->container = $container->getContainer();
        } else {
            $container = new ModuleContainer();
            $this->container = $container->getContainer();
        }
        parent::__construct();
        
        $this->identifier = 'id_order';
        $this->bootstrap = true;
        $this->table = 'orders';
        $this->className = 'Production';
        $this->lang = false;
        $this->_defaultOrderBy = 'id_order';
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';
        $this->identifier = 'id_order';
        //disable search, filters
        $this->simple_header = true;
        $this->shopLinkType = '';
        $this->bulk_actions = array(
            'send' => array(
                'text' => $this->l('Send selected'),
                'confirm' => $this->l('Send selected?'),
                'icon' => 'icon-envelope'
            )
        );
        
        
        $this->helperList = $this->container->get('helperList');
        $this->helperList->actions = array('send');
        $this->addRowAction('send');
        $this->fields_list = $this->helperList->getFieldsList();
        
        $this->reportsList = $this->container->get('reportsList');
    }
    
    public function setOrderBy($value)
    {
        $this->_orderBy = $value;
    }
    
    public function setOrderWay($value)
    {
        $this->_orderWay = strtoupper($value);
    }
    
    public function setFilters($aFilters = [])
    {
        $this->filters = $aFilters;
    }
    
    public function displaySendLink($token = '', $idOrder = '', $name = '')
    {
        $link = '
            <a href="index.php?controller=AdminProduction&amp;token='.$token.'&amp;id_order='.$idOrder.'&amp;send=1" title="Send" class="send btn btn-default">
                <i class="icon-envelope"></i> Send
            </a>
        ';
        return $link;
    }
    
    public function postProcess()
    {
        $url = $_SERVER['HTTP_REFERER'];
        if (Tools::getValue('send') == '1' || $this->action == 'bulksend') {
            $this->sendingReports();
            Tools::redirectAdmin($url);
        }
        
        if (Tools::isSubmit('report_sent'.$this->table)) {
            $id_order = (int)Tools::getValue('id_order');
            $this->initProductionOrder($id_order);
            $status = !(bool)$this->productionOrder->order->report_sent;
            $this->productionOrder->setStatus($status);
            Tools::redirectAdmin($url);
        }
        
        parent::postProcess();
    }
    
    public function sendingReports()
    {
        $ordersIds = [];
        if (Tools::getValue('send') == '1' && !empty(Tools::getValue('id_order'))) {
            $ordersIds[] = (int)Tools::getValue('id_order');
        } 
        
        if (!empty($this->boxes) && is_array($this->boxes) && $this->action == 'bulksend') {
            foreach ($this->boxes as $orderId) {
                $ordersIds[] = (int)$orderId;
            }
        }
        
        $this->sendReports($ordersIds);
    }
    
    public function sendReports($ordersIds = [])
    {
        if (empty($ordersIds) || !is_array($ordersIds)) {
            return;
        }
        
        $manufacturersReports = $this->reportsList->getManufacturersReports($ordersIds);
        
        if (empty($manufacturersReports) || !is_array($manufacturersReports)) {
            return;
        }
        
        foreach ($manufacturersReports as $manufacturerReport) {
            $manufacturerReport->sendReport();
        }
    }
    
    public function initProductionOrder($idOrder)
    {
        if (!empty($idOrder) && is_int($idOrder)) {
            $this->container->setParameter('apcontroller.idOrder', $idOrder);
            $this->productionOrder = $this->container->get('productionOrder');
        }
    }
    
    public function printManufacturer($manufacturerId)
    {
        //$this->initProductionOrder((int) $this->id);
        $manufacturer = new Manufacturer($manufacturerId);
        return $manufacturer->name;
    }
    
    public function printOrderState($id_order_state, $tr)
    {
        $this->initProductionOrder($tr['id_order']);
        return $this->productionOrder->orderState->name;
    }
    
    /**
     * Get the current objects' list form the database
     *
     * @param integer $id_lang Language used for display
     * @param string $order_by ORDER BY clause
     * @param string $_orderWay Order way (ASC, DESC)
     * @param integer $start Offset in LIMIT clause
     * @param integer $limit Row count in LIMIT clause
     */
    public function getList($id_lang)
    {
        $this->container->setParameter('tableName', $this->list_id);
        $pagination = $this->container->get('pagination');
        
        $ordrersIds = $this->getAllOrdersIds();
        $productionOrders = $this->helperList->getProductionOrders($ordrersIds);
        $productionOrders = $this->helperList->filteringProductionOrders($productionOrders, $this->filters);
        $productionOrders = $this->helperList->sortProductionOrders($productionOrders, $this->_orderBy, $this->_orderWay);
        $this->_listTotal = count($productionOrders);
        $productionOrders = array_slice($productionOrders, $pagination->start, $pagination->limit);
        $this->_list = $productionOrders;
        return $this->_list;
    }
    
    /**
     * Set the filters used for the list display
     */
    public function processFilter()
    {
        parent::processFilter();        
        $prefix = str_replace(array('admin', 'controller'), '', Tools::strtolower(get_class($this)));
        $filtersFamily = $this->context->cookie->getFamily($prefix.$this->list_id.'Filter_');
        $aFilters = $this->helperList->getFilters($filtersFamily, $prefix, $this->list_id);
        $this->setFilters($aFilters);
        
    }

    public function getHtmlList($list = [], $fields_list = [], $parrent = false)
    {
        if ($parrent === true) {
            return parent::renderList();
        } else {
            return $this->helperList->generateList($list, $fields_list);
        }
        
    }

    public function setSmartyTemplate()
    {
        $manufacturers = Manufacturer::getManufacturers();
        $current_tab = Tools::getValue('current_tab');
        $ordersList = $this->getHtmlList($this->_list, $this->fields_list, true);
        
        $weekStart = date("Y-m-d", strtotime("last Monday"));
        $weekEnd = date("Y-m-d", strtotime("Sunday"));
        
        $productionOrderStates = $this->helperList->getProductionOrderStates();
        
        $this->context->smarty->assign([
            'ordersList' => $ordersList,
            'manufacturers' => $manufacturers,
            'current_tab' => $current_tab,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'production_order_states' => $productionOrderStates,
        ]);
        $this->setTemplate('production.tpl');
    }
    
    public function initContent()
    {
        parent::initContent();
        $this->setSmartyTemplate();
    }
    
    public function getAllOrdersIds()
    {
        $ordersArray = Order::getOrdersWithInformations();
        $ordersIds = [];
        foreach ($ordersArray as $order) {
            $ordersIds[] = (int) $order['id_order'];
        }
        return $ordersIds;
    }
}
