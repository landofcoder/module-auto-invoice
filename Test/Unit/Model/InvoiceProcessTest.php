<?php

namespace Lof\AutoInvoice\Test\Unit\Model;

use ArrayIterator;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice as OrderInvoice;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as OrderStatusCollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Service\InvoiceServiceFactory;

use Lof\AutoInvoice\Api\Data\InvoiceProcessItemInterface;
use Lof\AutoInvoice\Api\Data\InvoiceProcessItemInterfaceFactory;
use Lof\AutoInvoice\Api\InvoiceProcessInterface;
use Lof\AutoInvoice\Helper\Data as HelperData;
use Lof\AutoInvoice\Model\InvoiceProcess;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceProcessTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var HelperData|PHPUnit_Framework_MockObject_MockObject
     */
    private $helperDataMock;
    
    /**
     * @var OrderCollectionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $orderCollectionFactoryMock;
    
    /**
     * @var OrderStatusCollectionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $orderStatusCollectionFactoryMock;
    
    /**
     * @var InvoiceProcessItemInterfaceFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceProcessItemFactoryMock;
    
    /**
     * @var Transaction|PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionMock;
    
    /**
     * @var InvoiceServiceFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceServiceFactoryMock;
    
    /**
     * @var InvoiceProcess
     */
    private $invoiceProcess;
    
    protected function setUp()
    {
        $this->helperDataMock = $this->getMockBuilder(HelperData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderCollectionFactoryMock = $this->getMockBuilder(OrderCollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->orderStatusCollectionFactoryMock = $this->getMockBuilder(OrderStatusCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        
        $this->invoiceProcessItemFactoryMock = $this->getMockBuilder(InvoiceProcessItemInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        
        $this->transactionMock = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceServiceFactoryMock = $this->getMockBuilder(InvoiceServiceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        
        $this->invoiceProcess = new InvoiceProcess(
            $this->helperDataMock,
            $this->orderCollectionFactoryMock,
            $this->orderStatusCollectionFactoryMock,
            $this->invoiceProcessItemFactoryMock,
            $this->transactionMock,
            $this->invoiceServiceFactoryMock
        );
    }

    /**
     * Test class service contract
     */
    public function testServiceContract()
    {
        $this->assertInstanceOf(
            InvoiceProcessInterface::class,
            $this->invoiceProcess
        );
    }
    
    /**
     * @covers \Lof\AutoInvoice\Model\InvoiceProcess::getItemsToProcess
     */
    public function testGetItemsToProcess()
    {
        $dstStatus = 'complete';
        $captureMode = 'offline';
        
        $this->helperDataMock->expects(self::once())
            ->method('getProcessingRules')
            ->willReturn([[
                HelperData::RULE_SOURCE_STATUS => 'processing',
                HelperData::RULE_PAYMENT_METHOD => HelperData::RULE_PAYMENT_METHOD_ALL,
                HelperData::RULE_DESTINATION_STATUS => $dstStatus,
                HelperData::RULE_CAPTURE_MODE => $captureMode,
            ]]);
        
        $orderCollectionMock = $this->getMockBuilder(OrderCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->orderCollectionFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($orderCollectionMock);
        
        $orderCollectionMock->expects(self::exactly(2))
            ->method('addFieldToFilter')
            ->willReturn($orderCollectionMock);
        
        $orders = [
            $this->getOrderMock(1, 'paypal'),
            $this->getOrderMock(2, 'paypal_express'),
            $this->getOrderMock(3, 'braintree'),
            $this->getOrderMock(4, 'braintree'),
            $this->getOrderMock(5, 'lof_stripe'),
        ];
        
        $orderCollectionMock->expects(self::once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator($orders));
        
        $items = [];
        foreach ($orders as $order) {
            $itemMock = $this->getMockForAbstractClass(InvoiceProcessItemInterface::class);
            
            $itemMock->expects(self::once())
                ->method('setOrder')
                ->with($order)
                ->willReturn($itemMock);
            
            $itemMock->expects(self::once())
                ->method('setDestinationStatus')
                ->with($dstStatus)
                ->willReturn($itemMock);
            
            $itemMock->expects(self::once())
                ->method('setCaptureMode')
                ->with($captureMode)
                ->willReturn($itemMock);
            
            $items[$order->getId()] = $itemMock;
        }
        
        $this->invoiceProcessItemFactoryMock->expects(self::exactly(count($items)))
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$items);
        
        $this->assertEquals(
            $this->invoiceProcess->getItemsToProcess(),
            $items
        );
    }
    
    /**
     * @covers \Lof\AutoInvoice\Model\InvoiceProcess::getItemsToProcess
     */
    public function testGetItemsToProcessPaymentMethods()
    {
        $srcStatus = 'processing';
        $dstStatusPaypal = 'complete';
        $captureModePaypal = 'offline';
        $dstStatusBraintree = 'processing';
        $captureModeBraintree = 'online';
        
        $this->helperDataMock->expects(self::once())
            ->method('getProcessingRules')
            ->willReturn([[
                HelperData::RULE_SOURCE_STATUS => $srcStatus,
                HelperData::RULE_PAYMENT_METHOD => 'paypal',
                HelperData::RULE_DESTINATION_STATUS => $dstStatusPaypal,
                HelperData::RULE_CAPTURE_MODE => $captureModePaypal,
            ], [
                HelperData::RULE_SOURCE_STATUS => $srcStatus,
                HelperData::RULE_PAYMENT_METHOD => 'braintree',
                HelperData::RULE_DESTINATION_STATUS => $dstStatusBraintree,
                HelperData::RULE_CAPTURE_MODE => $captureModeBraintree,
            ]]);
        
        $paypalOrders = [
            $this->getOrderMock(1, 'paypal'),
        ];
        $braintreeOrders = [
            $this->getOrderMock(3, 'braintree'),
            $this->getOrderMock(4, 'braintree')
        ];
        $otherOrders = [
            $this->getOrderMock(2, 'paypal_express'),
            $this->getOrderMock(5, 'lof_stripe'),
        ];
        
        $allData = [
            [$dstStatusPaypal, $captureModePaypal, $paypalOrders],
            [$dstStatusBraintree, $captureModeBraintree, $braintreeOrders],
        ];
        
        $items = [];
        $orderCollectionMocks = [];
        
        foreach ($allData as $data) {
            $dstStatus = $data[0];
            $captureMode = $data[1];
            $orders = $data[2];
            
            $orderCollectionMock = $this->getMockBuilder(OrderCollection::class)
                ->disableOriginalConstructor()
                ->getMock();
            
            $orderCollectionMock->expects(self::exactly(2))
                ->method('addFieldToFilter')
                ->withConsecutive(
                    ['status', ['eq' => $srcStatus]],
                    ['total_invoiced', ['null' => true]]
                )
                ->willReturnOnConsecutiveCalls($orderCollectionMock, $orderCollectionMock);
            
            $orderCollectionMock->expects(self::once())
                ->method('getIterator')
                ->willReturn(new ArrayIterator(array_merge($orders, $otherOrders)));
            
            $orderCollectionMocks []= $orderCollectionMock;
            
            foreach ($orders as $order) {
                $itemMock = $this->getMockForAbstractClass(InvoiceProcessItemInterface::class);
                $itemMock->expects(self::once())
                    ->method('setOrder')
                    ->with($order)
                    ->willReturn($itemMock);
                
                $itemMock->expects(self::once())
                    ->method('setDestinationStatus')
                    ->with($dstStatus)
                    ->willReturn($itemMock);
                
                $itemMock->expects(self::once())
                    ->method('setCaptureMode')
                    ->with($captureMode)
                    ->willReturn($itemMock);
                
                $items[$order->getId()] = $itemMock;
            }
        }
        
        $this->orderCollectionFactoryMock->expects(self::exactly(count($orderCollectionMocks)))
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$orderCollectionMocks);
        
        $this->invoiceProcessItemFactoryMock->expects(self::exactly(count($items)))
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$items);
        
        $this->assertEquals(
            $this->invoiceProcess->getItemsToProcess(),
            $items
        );
    }
    
    /**
     * Returns new mock order with given payment method
     */
    private function getOrderMock(int $orderId, string $paymentMethod)
    {
        $methodInstanceMock = $this->getMockForAbstractClass(\Magento\Payment\Model\MethodInterface::class);
        
        $methodInstanceMock->expects(self::any())
            ->method('getCode')
            ->willReturn($paymentMethod);
        
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $paymentMock->expects(self::any())
            ->method('getMethodInstance')
            ->willReturn($methodInstanceMock);
        
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $orderMock->expects(self::any())
            ->method('getId')
            ->willReturn($orderId);
        
        $orderMock->expects(self::any())
            ->method('getPayment')
            ->willReturn($paymentMock);
        
        return $orderMock;
    }
    
    /**
     * @covers \Lof\AutoInvoice\Model\InvoiceProcess::invoice
     */
    public function testInvoiceOffline()
    {
        $status = 'complete';
        $captureMode = 'offline';
        
        $orderStatusCollectionMock = $this->getMockBuilder(OrderStatusCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $orderStatusCollectionMock->expects(self::once())
            ->method('joinStates')
            ->willReturn($orderStatusCollectionMock);
        
        $this->orderStatusCollectionFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($orderStatusCollectionMock);
        
        $statuses = [
            $this->getOrderStatusMock('processing', 'processing'),
            $this->getOrderStatusMock('pending', 'pending'),
            $this->getOrderStatusMock('complete', 'complete'),
            $this->getOrderStatusMock('closed', 'closed'),
        ];
        
        $orderStatusCollectionMock->expects(self::once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator($statuses));
        
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $orderMock->expects(self::once())
            ->method('setStatus')
            ->with($status)
            ->willReturn($orderMock);
        
        $orderMock->expects(self::once())
            ->method('setState')
            ->with($status)
            ->willReturn($orderMock);
        
        $itemMock = $this->getMockForAbstractClass(InvoiceProcessItemInterface::class);
        
        $itemMock->expects(self::once())
            ->method('getOrder')
            ->willReturn($orderMock);
        
        $itemMock->expects(self::once())
            ->method('getDestinationStatus')
            ->willReturn($status);
        
        $itemMock->expects(self::once())
            ->method('getCaptureMode')
            ->willReturn($captureMode);
        
        $invoiceMock = $this->getMockBuilder(OrderInvoice::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRequestedCaptureCase', 'register'])
            ->getMock();
        
        $invoiceServiceMock = $this->getMockBuilder(InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $invoiceServiceMock->expects(self::once())
            ->method('prepareInvoice')
            ->with($orderMock)
            ->willReturn($invoiceMock);
        
        $this->invoiceServiceFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($invoiceServiceMock);
        
        $invoiceMock->expects(self::once())
            ->method('setRequestedCaptureCase')
            ->with($captureMode);
        
        $invoiceMock->expects(self::once())
            ->method('register');
        
        $this->transactionMock->expects(self::exactly(2))
            ->method('addObject')
            ->willReturn($this->transactionMock);
        
        $this->transactionMock->expects(self::once())
            ->method('save');
        
        $this->invoiceProcess->invoice($itemMock);
    }
    
    /**
     * @covers \Lof\AutoInvoice\Model\InvoiceProcess::invoice
     */
    public function testInvoiceOnline()
    {
        $status = 'complete';
        $captureMode = 'online';
        
        $orderStatusCollectionMock = $this->getMockBuilder(OrderStatusCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $orderStatusCollectionMock->expects(self::once())
            ->method('joinStates')
            ->willReturn($orderStatusCollectionMock);
        
        $this->orderStatusCollectionFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($orderStatusCollectionMock);
        
        $statuses = [
            $this->getOrderStatusMock('processing', 'processing'),
            $this->getOrderStatusMock('pending', 'pending'),
            $this->getOrderStatusMock('complete', 'complete'),
            $this->getOrderStatusMock('closed', 'closed'),
        ];
        
        $orderStatusCollectionMock->expects(self::once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator($statuses));
        
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $orderMock->expects(self::once())
            ->method('setStatus')
            ->with($status)
            ->willReturn($orderMock);
        
        $orderMock->expects(self::once())
            ->method('setState')
            ->with($status)
            ->willReturn($orderMock);
        
        $itemMock = $this->getMockForAbstractClass(InvoiceProcessItemInterface::class);
        
        $itemMock->expects(self::once())
            ->method('getOrder')
            ->willReturn($orderMock);
        
        $itemMock->expects(self::once())
            ->method('getDestinationStatus')
            ->willReturn($status);
        
        $itemMock->expects(self::once())
            ->method('getCaptureMode')
            ->willReturn($captureMode);
        
        $invoiceMock = $this->getMockBuilder(OrderInvoice::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRequestedCaptureCase', 'register'])
            ->getMock();
        
        $invoiceServiceMock = $this->getMockBuilder(InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $invoiceServiceMock->expects(self::once())
            ->method('prepareInvoice')
            ->with($orderMock)
            ->willReturn($invoiceMock);
        
        $this->invoiceServiceFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($invoiceServiceMock);
        
        $invoiceMock->expects(self::once())
            ->method('setRequestedCaptureCase')
            ->with($captureMode);
        
        $invoiceMock->expects(self::once())
            ->method('register');
        
        $this->transactionMock->expects(self::exactly(2))
            ->method('addObject')
            ->willReturn($this->transactionMock);
        
        $this->transactionMock->expects(self::once())
            ->method('save');
        
        $this->invoiceProcess->invoice($itemMock);
    }
    
    /**
     * Returns new mock status with given a status/state pair
     */
    private function getOrderStatusMock(string $status, string $state)
    {
        $orderStatusMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Status::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus', 'getState'])
            ->getMock();
        
        $orderStatusMock->expects(self::once())
            ->method('getStatus')
            ->willReturn($status);
        
        $orderStatusMock->expects(self::once())
            ->method('getState')
            ->willReturn($state);
        
        return $orderStatusMock;
    }
}
