<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Cron\Order\Invoice;

class Upload
{
    public $logger;
    public $config;
    public $orderFactory;
    public $collectionFactory;
    public $invoice;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Ced\Amazon\Helper\Order\Invoice $invoice,
        \Ced\Amazon\Helper\Logger $logger,
        \Ced\Amazon\Helper\Config $config
    ) {
        $this->orderFactory = $orderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->invoiceHelper = $invoice;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute()
    {
        if ($this->config->amazonInvoiceUpload() == true && $this->config->invoiceCron() == true) {
            try {
                $orderIds = [];
                $now = date("Y-m-d");
                $start = date('Y-m-d', strtotime('-10 days', strtotime($now)));

                $collections = $this->collectionFactory->create()
                    ->addFieldToFilter(
                        \Ced\Amazon\Model\Order::COLUMN_STATUS,
                        [
                            'in' => [
                                \Ced\Amazon\Model\Source\Order\Status::SHIPPED,
                            ]
                        ]
                    )
                    ->addFieldToFilter(\Ced\Amazon\Model\Order::COLUMN_PO_DATE, ['from' => $start, 'to' => $now])
                    ->addFieldToFilter(\Ced\Amazon\Model\Order::COLUMN_INVOICE_UPLOAD_STATUS, ['eq' => 0])
                    ->addFieldToFilter(\Ced\Amazon\Model\Order::COLUMN_MARKETPLACE_ID, ['in' =>[
                        'A1RKKUPIHCS9HS',
                        'A13V1IB3VIYZZH',
                        'A1PA6795UKMFR9',
                        'APJ6JRA9NG5V4',
                        'A1805IZSGTT6HS',
                        'A1F83G8C2ARO7P',
                        ]])->setOrder('magento_order_id', 'ASC')->setPageSize(10);
                if (isset($collections)) {
                    foreach ($collections as $collection) {
                        $orderIds[] = $collection->getMagentoOrderId();
                    }
                }
                if (isset($orderIds)) {
                    $this->invoiceHelper->upload($orderIds);
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
            }
        }
    }
}
