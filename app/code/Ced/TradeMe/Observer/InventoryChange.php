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
 * @category    Ced
 * @package     Ced_TradeMe
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\TradeMe\Observer;

use Ced\TradeMe\Model\ResourceModel\Profile;
use Magento\Framework\Event\ObserverInterface;

class InventoryChange implements ObserverInterface
{
    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Data Helper
     * @var \Ced\TradeMe\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Ced\TradeMe\Helper\MultiAccount
     */
    public $multiaccounthelper;
    public $profileCollection;

    /**
     * ProductSaveAfter constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request,
        \Ced\TradeMe\Model\ResourceModel\Profile\CollectionFactory $profileCollection,
        \Ced\TradeMe\Helper\MultiAccount $multiaccounthelper
    ) {
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->profileCollection = $profileCollection;
        $this->multiaccounthelper = $multiaccounthelper;
    }

    /**
     * Catalog product save after event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return boolean
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getItem();
            $accountId = $this->multiaccounthelper->getAllaccounts();

            if (empty($product)) {
                return $observer;
            }

            $productids[] = $product->getId();
            $productId = $product->getProductId();
            //capture stock change
            $orgQty = $product->getOrigData('quantity_and_stock_status');
            $oldValue = (int)$orgQty['qty'];

            $postData = $this->request->getParam('product');
            $newValue = (int)$postData['quantity_and_stock_status']['qty'];

            $isInStock = (boolean)$postData['quantity_and_stock_status']['is_in_stock'];
            //if out of stock then set value to 0
            if (!$isInStock) {
                $newValue = 0;
            }

            if ($oldValue == $newValue) {
                return $observer;
            }
            foreach ($accountId->getData() as $id) {
                $accId = $id['id'];
                $model = $this->objectManager->create('Ced\TradeMe\Model\Productchange');

                $type = \Ced\TradeMe\Model\Productchange::CRON_TYPE_INVENTORY;

                $model->setProductChange($productId, $oldValue, $newValue, $type, $accId);

            }
            return $observer;
        } catch (\Exception $e) {
            return $observer;
        }
    }

}
