<?php

namespace Ced\MPCatch\Observer;

class Refund implements \Magento\Framework\Event\ObserverInterface
{
	protected $objectManager;
	protected $api;
	protected $logger;

	public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\MPCatch\Helper\Logger $logger,
        \Ced\MPCatch\Helper\Order $api,
        \Ced\MPCatch\Model\OrdersFactory $orders,
        \Ced\MPCatch\Helper\Config $config,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->objectManager = $objectManager;
        $this->api = $api;
        $this->logger = $logger;
        $this->orders = $orders;
        $this->config = $config;
        $this->json = $json;
        $this->messageManager = $manager;
        $this->_request = $request;
    }
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        $this->logger->log('INFO','Refund Observer Working');
        $refundOnCatch = $this->config->getRefundOnCatch();
        $refundSkus = [];
        try {
            if ($refundOnCatch == "1") {
                $postData = $this->_request->getParams();
                if(isset($postData['order_id'])) {
                    $reason = (isset($postData['reason']) && $postData['reason'] != NULL) ? $postData['reason'] : $this->config->getRefundReason();
                    $creditMemo = $observer->getEvent()->getCreditmemo();
                    $creditMemoId = $creditMemo->getIncrementId();
                    $order = $creditMemo->getOrder();
                    $orderIncrementId = $order->getIncrementId();
                    $catchorder = $this->orders->create()->getCollection()->addFieldToFilter('increment_id', $orderIncrementId)->getFirstItem()->getData();
                    if (count($catchorder) <= 0) {
                        return $observer;
                    }
                    if (!$reason) {
                        $this->messageManager->addErrorMessage('Catch Refund Reason is not selected.');
                        return $observer;
                    }
                    $item = array();
                    $cancelOrder = array(
                        'refund' => array(
                            '_attribute' => array(),
                            '_value' => array()
                        )
                    );
                    $catchorder_data = $this->json->jsonDecode($catchorder['order_data']);
                    $catchorder_data = $catchorder_data['order_lines']['order_line'];
                    $order_line_ids = array_column($catchorder_data, 'offer_sku');
                    foreach ($creditMemo->getAllItems() as $orderItems) {
                        $skuFound = array_search($orderItems->getSku(), $order_line_ids);
                        if ($skuFound !== FALSE) {
                            $refundSkus[] = $orderItems->getSku();
                            $item['amount'] = (string)$orderItems->getRowTotal();
                            $item['order_line_id'] = (string)$catchorder_data[$skuFound]['order_line_id'];
                            $item['quantity'] = (string)$orderItems->getQty();
                            $item['reason_code'] = (string)$reason;
                            $item['shipping_amount'] = (string)((float)$catchorder_data[$skuFound]['shipping_price'] / (float)$orderItems->getQty());
                        }
                        array_push($cancelOrder['refund']['_value'], $item);
                    }
                    $response = $this->api->refundOnCatch($orderIncrementId, $cancelOrder, /*$creditMemoId*/
                        $order->getId());

                    $this->logger->info('Refund Observer Data', ['path' => __METHOD__, 'DataToRefund' => json_encode($cancelOrder), 'Response Data' => json_encode($response)]);

                    if (isset($response['body']['refunds'])) {
                        $refundSkus = implode(', ', $refundSkus);
                        $order->addStatusHistoryComment(__("Order Items ( $refundSkus ) Refunded with $reason reason On Catch."))
                            ->setIsCustomerNotified(false)->save();
                        $this->logger->info('Refund Success', ['path' => __METHOD__, 'RefundSkus' => $refundSkus, 'Reason' => $reason, 'Increment Id' => $orderIncrementId]);
                        $this->messageManager->addSuccessMessage('Refund Successfully Generated on Catch');
                    } else {
                        $this->logger->info('Refund Fail', ['path' => __METHOD__, 'DataToRefund' => json_encode($cancelOrder), 'Response Data' => json_encode($response)]);
                        $this->messageManager->addErrorMessage('Error Generating Refund on Catch. Please process from merchant panel.');
                    }
                }
                return $observer;
            }
        } catch (\Exception $e) {
            $this->logger->error('Refund Observer', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $observer;
        }
        return $observer;
	}
}