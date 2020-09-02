<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_GXpress
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Plugin\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Ced\GXpress\Model\ResourceModel\Orders\CollectionFactory;

class Get
{
    /** @var CollectionFactory */
    public $orderCollectionFactory;

    /** @var OrderExtensionFactory  */
    public $orderExtensionFactory;

    public function __construct(
        OrderExtensionFactory $extensionFactory,
        CollectionFactory $orderFactory
    ) {
        $this->orderCollectionFactory = $orderFactory;
        $this->orderExtensionFactory = $extensionFactory;
    }

    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        $resultOrder = $this->getMarketplaceOrderIdAttribute($resultOrder);

        return $resultOrder;
    }

    private function getMarketplaceOrderIdAttribute(\Magento\Sales\Api\Data\OrderInterface $order)
    {

        try {
            /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection */
            $collection = $this->orderCollectionFactory
                ->create()
                ->addFieldToSelect(['gxpress_order_id'])
                ->addFieldToFilter('magento_order_id', ['eq' => $order->getIncrementId()])
                ->setPageSize(1)
                ->setCurPage(1);
            if ($collection->getSize() > 0) {
                $marketplaceOrderIdAttributeValue = $collection->getFirstItem()->getData('gxpress_order_id');
            } else {
                throw new \Exception('Order not found in GXpress');
            }
        } catch (\Exception $e) {
            return $order;
        }

        $extensionAttributes = $order->getExtensionAttributes();
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $orderExtension->setGxpressOrderId($marketplaceOrderIdAttributeValue);
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }
}
