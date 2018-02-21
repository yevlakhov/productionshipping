<?php

/**
 * Created by PhpStorm.
 * User: tsiger
 * Date: 30.04.16
 * Time: 13:09
 */

use \Mockery as m;

class ProductionOrderTest extends PHPUnit_Framework_TestCase
{
    
    protected $productionOrder;
    
    public function tearDown()
    {
        m::close();
    }
    
    protected function setUp()
    {
        // example of mocking function with argument
        /*$customer = $this->getMockBuilder('Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->any())
            ->method('getAddresses')
            ->with(1)
            ->willReturn([
                [
                    'alias' => 'My address 1',
                    'address1' => '904 6th St',
                    'postcode' => '98224',
                    'city' => 'Anacortes',
                    'state' => 'Washington',
                    'country' => 'United States',
                ],
                [
                    'alias' => 'My address 2',
                    'address1' => '58 6th St',
                    'postcode' => '23456',
                    'city' => 'New York',
                    'state' => 'New York',
                    'country' => 'United States',
                ]
            ]);*/
        
        $this->order = $this->getMockBuilder('Order')
            ->setConstructorArgs([10])
            ->getMock();
        //$this->productionOrder->order = $order;
        
        //cart object is deprecated
        /*$cart = $this->getMockBuilder('Cart')
            ->setConstructorArgs([1])
            ->getMock();
        $this->productionOrder->cart = $cart;*/
        
        $this->address = $this->getMockBuilder('Address')
            ->setConstructorArgs([10])
            ->getMock();
        //$this->productionOrder->address = $address;
        
        $this->customer = $this->getMockBuilder('Customer')
            ->disableOriginalConstructor()
            ->getMock();
        //$this->productionOrder->customer = $customer;
        
        $this->mockery_productionOrder = m::mock('ProductionOrder')->makePartial();
        $this->mockery_productionOrder->testEnv = true;
        $this->mockery_productionOrder->order = $this->order;
        $this->mockery_productionOrder->address = $this->address;
        $this->mockery_productionOrder->customer = $this->customer;
        
        //for testing init methods
        //$this->productionOrder = m::mock('ProductionOrder[""]', array(10, false))->makePartial();
    }
    
    /*public function testGetOrdersArray()
    {
        $ordersArray = $this->productionOrder->getOrdersArray();
        $this->assertEquals(true, is_array($ordersArray));
    }*/
    
    /*public function testGetAllOrders()
    {
        $ordersList = $this->productionOrder->getAllOrders();
        $this->assertEquals(true, is_array($ordersList));
    }*/
    
    /*public function testGetOrdersByManufacturer()
    {
        $ordersList = $this->productionOrder->getOrdersByManufacturer();
        $this->assertEquals(true, is_array($ordersList));
    }*/
    
    public function getCompanyNameProvider()
    {
        return array(
            ['', ''],
            ['a', 'a'],
            ['', null],
            ['1', 1,],
            ['', 0],
            ['-1', -1],
            ['', array()],
            ['1', true],
            ['', false],
        );
    }
    
    /**
     *
     * @dataProvider getCompanyNameProvider
     */
    public function testGetCompanyName($result, $company)
    {
        $this->mockery_productionOrder->customer->company = $company;
        $res = $this->mockery_productionOrder->getCompanyName();
        $this->assertEquals($result, $res);
    }

    public function getCustomerNameProvider()
    {
        //$myObject = (object)array();
        return array(
            ['', '', ''],
            ['a a', 'a', 'a'],
            ['', null, 'a'],
            ['1 2', 1, 2,],
            ['', 0, 1],
            ['2 -1', 2, -1],
            ['', array(), array()],
            ['1 a', true, 'a'],
            ['', false, 'a'],
            //[$myObject, $myObject, ''],
        );
    }

    /**
     *
     * @dataProvider getCustomerNameProvider
     */
    public function testGetCustomerName($result, $firstname, $lastname)
    {        
        $this->mockery_productionOrder->customer->firstname = $firstname;
        $this->mockery_productionOrder->customer->lastname = $lastname;
        $res = $this->mockery_productionOrder->getCustomerName();
        $this->assertEquals($result, $res);
    }
    
    public function getDaysLeftProvider()
    {
        return array(
            ['10', new DateTime('05/10/2016'), '05/20/2016'],
            ['1', new DateTime('2016-05-20 13:42:48'), '05/21/2016'],
            ['0', new DateTime('05/25/2016'), '05/20/2016'],
            ['0', new DateTime('05/20/2016'), '05/20/2016'],
            ['0', new DateTime('05/20/2016'), ''],
            ['0', new DateTime('05/20/2016'), array(1, 2)],
            ['0', new DateTime('05/20/2016'), new stdClass()],
        );
    }
    
    /**
     *
     * @dataProvider getDaysLeftProvider
     */
    public function testGetDaysLeft($result, $currentDate, $shippingDate)
    {
        $this->mockery_productionOrder->currentDate = $currentDate;
        $this->mockery_productionOrder->shippingDate = $shippingDate;
        $res = $this->mockery_productionOrder->getDaysLeft();
        $this->assertEquals($result, $res);
    }
    
    public function getShippingDateProvider()
    {
        return array(
            ['05/26/2016', new DateTime('5/12/2016'), 14, 'm/d/Y'],
            
            ['', new DateTime('00/00/0000'), 14, 'm/d/Y'],
            ['', new DateTime('5/12/2016'), '', 'm/d/Y'],
            ['', '5/12/2016', '14', 'm/d/Y'],
            ['', '', '14', 'm/d/Y'],
            ['', 'aaa', '14', 'm/d/Y'],
            ['', 1, '14', 'm/d/Y'],
            ['', 0, '14', 'm/d/Y'],
            ['', -1, '14', 'm/d/Y'],
            ['', null, '14', 'm/d/Y'],
            ['', array(1, 2), '14', 'm/d/Y'],
            ['', array(), '14', 'm/d/Y'],
            ['', false, '14', 'm/d/Y'],
            ['', true, '14', 'm/d/Y'],
            
            ['', new DateTime('5/12/2016'), '', 'm/d/Y'],
            ['', new DateTime('5/12/2016'), 'aaa', 'm/d/Y'],
            ['', new DateTime('5/12/2016'), null, 'm/d/Y'],
            ['Wrong interval', new DateTime('5/12/2016'), -1, 'm/d/Y'],
            ['', new DateTime('5/12/2016'), 0, 'm/d/Y'],
            ['', new DateTime('5/12/2016'), array(1, 2), 'm/d/Y'],
            ['', new DateTime('5/12/2016'), false, 'm/d/Y'],
            ['', new DateTime('5/12/2016'), true, 'm/d/Y'],
            
            ['', new DateTime('5/12/2016'), 14, ''],
            ['', new DateTime('5/12/2016'), 14, null],
            ['', new DateTime('5/12/2016'), 14, false],
            //['', new DateTime('5/12/2016'), 14, new stdClass()],
            //['', new DateTime('5/12/2016'), 14, array(1, 2)],
            //['', new DateTime('5/12/2016'), 14, true],
        );
    }
    
    /**
     *
     * @dataProvider getShippingDateProvider
     */
    public function testGetShippingDate($result, $sentDate, $shippingPeriod, $format)
    {
        //It doesn't work, because of it I use Mockery
        /*$this->productionOrder->method('getSentDate')
            ->willReturn($sentDate);
        $this->productionOrder->method('getShippingPeriod')
            ->willReturn($shippingPeriod);
        
        $res = $this->productionOrder->getShippingDate($format);*/
        
        $this->mockery_productionOrder->shouldReceive('getSentDate')->andReturn($sentDate);
        $this->mockery_productionOrder->shouldReceive('getShippingPeriod')->andReturn($shippingPeriod);
        $res = $this->mockery_productionOrder->getShippingDate($format);
        
        $this->assertEquals($result, $res);
    }
    
    public function getShippingAddressProvider()
    {
        return [
            ['a - a a a a a ', 'a', 'a', 'a', 'a', 'a', 'a', 'a'],
            ['', '', 'a', 'a', 'a', 'a', 'a', 'a'],
            ['a a a a a ', 'a', '', 'a', 'a', 'a', 'a', 'a'],
            ['a - a a a a ', 'a', 'a', '', 'a', 'a', 'a', 'a'],
        ];
    }
    
    /**
     *
     * @dataProvider getShippingAddressProvider
     */
    public function testGetShippingAddress($result, $id_address_delivery, $alias, $address1, $postcode, $city, $state, $country)
    {
        $this->mockery_productionOrder->order->id_address_delivery = $id_address_delivery;
        $this->mockery_productionOrder->address->alias = $alias;
        $this->mockery_productionOrder->address->address1 = $address1;
        $this->mockery_productionOrder->address->postcode = $postcode;
        $this->mockery_productionOrder->address->city = $city;
        $this->mockery_productionOrder->address->state = $state;
        $this->mockery_productionOrder->address->country = $country;
        
        $res = $this->mockery_productionOrder->getShippingAddress();
        $this->assertEquals($result, $res);
    }
    
    public function getProductsProvider()
    {
        return [
            ["a \n", [0  => ['product_reference' => 'a']]],
            
            ["", []],
            ["", [0  => []]],
            ["", [0  => ['product_reference' => '']]],
            ["", [0  => ['product_reference' => 1]]],
            ["", [0  => ['product_reference' => 0]]],
            ["", [0  => ['product_reference' => -1]]],
            ["", [0  => ['product_reference' => null]]],
            ["", [0  => ['product_reference' => array(1,2)]]],
            ["", [0  => ['product_reference' => array()]]],
            ["", [0  => ['product_reference' => true]]],
            ["", [0  => ['product_reference' => false]]],
            ["", [0  => ['product_reference' => new stdClass()]]],
        ];
    }
    
    /**
     *
     * @dataProvider getProductsProvider
     */
    public function testGetProducts($result, $products)
    {
        $this->order->method('getProducts')->willReturn($products);
        $this->mockery_productionOrder->order = $this->order;
        $res = $this->mockery_productionOrder->getProducts();
        $this->assertEquals(true, is_string($res));
        $this->assertEquals($result, $res);
    }
    
    public function getSentDateProvider()
    {
        return [
            [new DateTime('5/12/2016'), '5/12/2016', '6/12/2016'],
            [new DateTime('6/12/2016'), '', '6/12/2016'],
            [new DateTime('00/00/0000'), null, null],
            [new DateTime('00/00/0000'), 'aa', 'aa'],
            [new DateTime('00/00/0000'), '', ''],
            [new DateTime('00/00/0000'), 1, 1],
            [new DateTime('00/00/0000'), 0, 0],
            [new DateTime('00/00/0000'), -1, -1],
            [new DateTime('00/00/0000'), true, true],
            [new DateTime('00/00/0000'), false, false],
            [new DateTime('00/00/0000'), array(), array()],
            [new DateTime('00/00/0000'), array(1, 2), array(1, 2)],
            [new DateTime('00/00/0000'), new stdClass(), new stdClass()],
        ];
    }
    
    /**
     *
     * @dataProvider getSentDateProvider
     */
    public function testGetSentDate($result, $poSentDate, $saSentDate)
    {
        $this->mockery_productionOrder->poSentDate = $poSentDate;
        $this->mockery_productionOrder->saSentDate = $saSentDate;
        $res = $this->mockery_productionOrder->getSentDate();
        $this->assertEquals(true, ($res instanceof DateTime));
        $this->assertEquals($result, $res);
    }
    
    public function getShippingPeriodProvider()
    {
        return [
            [1, 1, 2, '5/12/2016', '6/12/2016'],
            [0, null, 2, '5/12/2016', '6/12/2016'],
            
            [0, null, null, '5/12/2016', '6/12/2016'],
            [0, 'aa', 'aa', '5/12/2016', '6/12/2016'],
            [0, '', '', '5/12/2016', '6/12/2016'],
            [0, 0, 0, '5/12/2016', '6/12/2016'],
            //[0, -1, -1, '5/12/2016', '6/12/2016'],
            [0, true, true, '5/12/2016', '6/12/2016'],
            [0, false, false, '5/12/2016', '6/12/2016'],
            [0, array(), array(), '5/12/2016', '6/12/2016'],
            [0, array(1, 2), array(1, 2), '5/12/2016', '6/12/2016'],
            [0, new stdClass(), new stdClass(), '5/12/2016', '6/12/2016'],
            
            [2, 1, 2, '', '6/12/2016'],
            [0, 1, 2, null, null],
            //[0, 1, 2, 'aa', 'aa'],
            [0, 1, 2, '', ''],
            [0, 1, 2, 1, 1],
            [0, 1, 2, 0, 0],
            [0, 1, 2, -1, -1],
            [0, 1, 2, true, true],
            [0, 1, 2, false, false],
            [0, 1, 2, array(), array()],
            [0, 1, 2, array(1, 2), array(1, 2)],
            [0, 1, 2, new stdClass(), new stdClass()],
        ];
    }
    
    /**
     *
     * @dataProvider getShippingPeriodProvider
     */
    public function testGetShippingPeriod($result, $poShippingPeriod, $saShippingPeriod, $poSentDate, $saSentDate)
    {
        $this->mockery_productionOrder->shouldReceive('getPoShippingPeriod')->andReturn($poShippingPeriod);
        $this->mockery_productionOrder->shouldReceive('getSaShippingPeriod')->andReturn($saShippingPeriod);
        
        $this->mockery_productionOrder->poSentDate = $poSentDate;
        $this->mockery_productionOrder->saSentDate = $saSentDate;
        
        $res = $this->mockery_productionOrder->getShippingPeriod();
        $this->assertEquals(true, is_int($res));
        $this->assertEquals($result, $res);
    }
    
    public function getPoShippingPeriodProvider()
    {
        return [
            [1, '1'],
            [0, ''],
            [0, 'a'],
            [0, null],
            [1, 1,],
            [0, 0],
            [-1, -1],
            [0, array()],
            [0, array(1, 2)],
            [0, true],
            [0, false],
            [0, new stdClass()],
        ];
    }
    
    /**
     *
     * @dataProvider getPoShippingPeriodProvider
     */
    public function testGetPoShippingPeriod($result, $poShippingPeriod)
    {
        $this->mockery_productionOrder->poShippingPeriod = $poShippingPeriod;
        $res = $this->mockery_productionOrder->getPoShippingPeriod();
        $this->assertEquals(true, is_int($res));
        $this->assertEquals($result, $res);
    }
    
    public function getSaShippingPeriodProvider()
    {
        return [
            [1, '1'],
            [0, ''],
            [0, 'a'],
            [0, null],
            [1, 1,],
            [0, 0],
            [-1, -1],
            [0, array()],
            [0, array(1, 2)],
            [0, true],
            [0, false],
            [0, new stdClass()],
        ];
    }
    
    /**
     *
     * @dataProvider getSaShippingPeriodProvider
     */
    public function testGetSaShippingPeriod($result, $saShippingPeriod)
    {
        $this->mockery_productionOrder->saShippingPeriod = $saShippingPeriod;
        $res = $this->mockery_productionOrder->getSaShippingPeriod();
        $this->assertEquals(true, is_int($res));
        $this->assertEquals($result, $res);
    }
    
    public function testGetRow()
    {
        $res = $this->mockery_productionOrder->getRow();
        $this->assertEquals(true, is_array($res));
        $this->assertArrayHasKey('id_order', $res);
        $this->assertArrayHasKey('customer', $res);
        $this->assertArrayHasKey('company', $res);
        $this->assertArrayHasKey('shipping_address', $res);
        $this->assertArrayHasKey('products', $res);
        $this->assertArrayHasKey('po_sent', $res);
        $this->assertArrayHasKey('sa_sent', $res);
        $this->assertArrayHasKey('shipping_date', $res);
        $this->assertArrayHasKey('days_left', $res);
    }
    
    public function initOrderProvider()
    {
        return [
            [true, false, 10],
            [false, true, 10],
        ];
    }
    
    /**
     *
     * @dataProvider initOrderProvider
     */
    public function testInitOrder($result, $testEnv, $idOrder)
    {
        $this->mockery_productionOrder->testEnv = $testEnv;
        $this->mockery_productionOrder->order = null;
        $this->mockery_productionOrder->initOrder($idOrder);
        $this->assertEquals($result, $this->mockery_productionOrder->order instanceof Order);
    }
    
    public function initCustomerProvider()
    {
        return [
            [true, false],
            [false, true],
        ];
    }
    
    /**
     *
     * @dataProvider initCustomerProvider
     */
    public function testInitCustomer($result, $testEnv)
    {
        $this->mockery_productionOrder->testEnv = $testEnv;
        $this->mockery_productionOrder->customer = null;
        $this->mockery_productionOrder->initOrder(10);
        $this->mockery_productionOrder->initCustomer();
        $this->assertEquals($result, $this->mockery_productionOrder->customer instanceof Customer);
    }

}
