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
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Observer\Product;

use Ced\Amazon\Api\Service\ConfigServiceInterface;
use Ced\Amazon\Model\Profile\Product;
use Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory;
use Magento\Framework\Event\ObserverInterface;
use Ced\Amazon\Helper\Product\Price;
use Ced\Amazon\Helper\Config;
use Ced\Amazon\Helper\Logger;
use Ced\Amazon\Helper\Product as ProductHelper;
use Magento\Framework\Exception\LocalizedException;

class Save implements ObserverInterface
{
    public $profileCollectionFactory;

    public $serviceProfileProduct;

    private $logger;

    private $config;

    private $price;

    private $product;

    private $productHelper;

    private $configuration;

    private $amazonProductRepository;

    private $uploadProduct;

    public function __construct(
        Logger $logger,
        Price $price,
        Config $config,
        \Ced\Amazon\Model\ResourceModel\Profile\Product $product,
        ProductHelper $productHelper,
        CollectionFactory $profileCollectionFactory,
        \Ced\Amazon\Repository\Profile\Product $amazonProductRepository,
        ConfigServiceInterface $configuration,
        ProductHelper $uploadProduct,
        \Ced\Amazon\Service\Profile\Product\Save $serviceProfileProduct
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->price = $price;
        $this->product = $product;
        $this->productHelper = $productHelper;
        $this->amazonProductRepository = $amazonProductRepository;
        $this->configuration = $configuration;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->uploadProduct = $uploadProduct;
        $this->serviceProfileProduct = $serviceProfileProduct;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            if ($event->hasData('product')) {
                $product = $event->getData('product');
                $productId = $product->getId();
                $product->getStoreId();
//                Auto add Product On Profile By category
                if ($this->configuration->autoAddOnProfile()) {
                    return $this->autoAddProductByCategory($product);
                }
                //Auto Price Sync when Price Change in Product
                if ($this->config->getPriceSync()) {
                    $productId = $product->getId();
                    if ($this->product->checkIfExists($productId) &&
                        $product->getData(\Magento\Catalog\Model\Product::PRICE) !=
                        $product->getOrigData(\Magento\Catalog\Model\Product::PRICE)) {
                        // Add Price Change to Queue
                        $this->price->update([$productId], true, \Ced\Amazon\Model\Source\Queue\Priorty::HIGH);
                    }

                }
            }
        } catch (\Exception $e) {
            // silence
        }
    }


    public function autoAddProductByCategory($product)
    {
        $productId = [$product->getId()];
        $categoryIds = $product->getCategoryIds();
//      $storeId = $product->getStoreId();
        if (isset($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $profileCollection = $this->profileCollectionFactory->create();
                $profileIds = $profileCollection->
                addFieldToFilter('magento_category', ['eq' => $categoryId]);
                //->addFieldToFilter('store_id', ['eq' => $storeId])
                if (isset($profileIds)) {
                    foreach ($profileIds as $profileId) {
                        $profileID = $profileId['id'];
                        $accountId = $profileId['account_id'];
                        $marketplace = $profileId['marketplace'];
                        try {
                            $productID = $this->serviceProfileProduct->
                            validateProfileProducts($productId, $marketplace, $accountId);
                        } catch (LocalizedException $e) {
                        }
                        if ($productID != null) {
                            $this->amazonProductRepository->
                            addProductsIdsWithProfileId($productID, $profileID);
                        }
                    }
                }
            }
        }
        try {
            if ($this->configuration->autoUploadOnAdd()) {
                $this->uploadProduct->update($productId,
                    $throttle = true, $operationType = \Amazon\Sdk\Base::OPERATION_TYPE_UPDATE);
            }
        } catch (\Exception $e) {
        }
    }
}
