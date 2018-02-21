<?php

use \Mockery as m;

class AdminProductionControllerTest extends PHPUnit_Framework_TestCase
{
    protected $productionController;
    protected $helperList;
    protected $container;
    
    protected function setUp()
    {
        $this->helperList = new HelperList();
        
        $container = new ModuleContainer();
        $this->container = $container;
        
        $this->productionController = new AdminProductionController($this->container);
        
        $productionOrder = $this->getMockBuilder('ProductionOrder')
            ->setConstructorArgs([1])
            ->getMock();
        //@todo: make test with various parrameters
        
        $productionOrder->method('getCustomerName')->willReturn('Customer Name');
        $productionOrder->method('getShippingAddress')->willReturn('My Address');
        $productionOrder->method('getProducts')->willReturn('Product name');
        $productionOrder->poSentDate = '';
        $productionOrder->saSentDate = '2016/01/01';
        $productionOrder->poShippingPeriod = '';
        $productionOrder->saShippingPeriod = '45';
        $productionOrder->method('getShippingDate')->willReturn('2016/02/01');
        $productionOrder->method('getDaysLeft')->willReturn('31');
        $this->productionController->productionOrder = $productionOrder;
        
        
        $this->mockery_productionController = m::mock('AdminProductionController')->makePartial();
    }


    public function testConstructorWithoutParrameters()
    {
        $productionController = new AdminProductionController();
        $this->assertEquals(true, $productionController instanceof AdminProductionController);
    }
    
    /*public function testSetFormConfig()
    {
        $this->productionController->setFormConfig();
        $this->assertEquals(true, $this->productionController->helperList->bootstrap);
        $this->assertEquals('order_detail', $this->productionController->helperList->table);
        $this->assertEquals('Production', $this->productionController->helperList->className);
        $this->assertEquals(false, $this->productionController->helperList->lang);
        $this->assertEquals('id_order', $this->productionController->helperList->_defaultOrderBy);
        $this->assertEquals('id_order', $this->productionController->helperList->_orderBy);
        $this->assertEquals('DESC', $this->productionController->helperList->_orderWay);
        $this->assertEquals('id_order', $this->productionController->helperList->identifier);
        $this->assertEquals(true, $this->productionController->helperList->simple_header);
    }*/
    
    public function testSetFieldsList()
    {
        $this->productionController->setFieldsList();
        $array = array(
            'id_order' => [],
            'customer' => [],
            'company' => [],
            'shipping_address' => [],
            'products' => [],
            'po_sent' => [],
            'sa_sent' => [],
            'shipping_date' => [],
            'days_left' => [],
        );
        $this->assertArraySubset($array, $this->productionController->fields_list);
    }
    
    public function testGetHtmlList()
    {
        $ordersList = $this->productionController->getList();
        $htmlList = $this->productionController->getHtmlList($ordersList, $this->productionController->fields_list);
        $this->assertContains('<form method="post" action="&amp;token=#order_detail"', $htmlList);
        
        $htmlList = $this->productionController->getHtmlList($ordersList, $this->productionController->fields_list, true);
        $this->assertContains('<form method="post" action="&amp;token=', $htmlList);
    }
    
    public function getListProvider()
    {
        $emptyRow = [
            'id_order' => '',
            'customer' => '',
            'company' => '',
            'shipping_address' => '',
            'products' => '',
            'po_sent' => '',
            'sa_sent' => '',
            'shipping_date' => '',
            'days_left' => '',
        ];
        
        return [
            [
                [['id_order' => '1']], [['id_order' => '1']], ['id_order' => '1'],
            ],
            [
                [0 => $emptyRow], [], [],
            ],
            [
                [0 => $emptyRow], 'aa', [],
            ],
            [
                [0 => $emptyRow], null, [],
            ],
            [
                [0 => $emptyRow], new stdClass(), [],
            ],
            [
                [0 => $emptyRow], true, [],
            ],
            
            
            
            [
                [0 => $emptyRow], [['id_order' => '1']], [],
            ],
            [
                [0 => $emptyRow], [['id_order' => '1']], 'aa',
            ],
            [
                [0 => $emptyRow], [['id_order' => '1']], null,
            ],
            [
                [0 => $emptyRow], [['id_order' => '1']], new stdClass(),
            ],
            [
                [0 => $emptyRow], [['id_order' => '1']], true,
            ],
            
            [
                [0 => $emptyRow], [['aa' => '1']], ['id_order' => '1'],
            ],
            [
                [0 => $emptyRow], [['id_order' => true]], ['id_order' => '1'],
            ],
        ];
    }
    
    /**
     *
     * @dataProvider getListProvider
     */
    public function testGetList($result, $ordersArray, $row)
    {
        $this->productionController->productionOrder->method('getRow')->willReturn($row);
        $this->mockery_productionController->productionOrder = $this->productionController->productionOrder;
        $this->mockery_productionController->shouldReceive('initProductionOrder')->andReturn(null);
        $this->mockery_productionController->shouldReceive('getOrdersArray')->andReturn($ordersArray);
        $res = $this->mockery_productionController->getList();
        $this->assertEquals($result, $res);
    }
    
    /*public function testSetSmartyTemplate()
    {
        $this->productionController->setSmartyTemplate();
        $orderList = $this->productionController->context->smarty['tpl_vars']->orderList;
        $this->assertEquals(true, isset($orderList));
    }*/
    
}