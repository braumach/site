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
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Helper\Product;

use Magento\Framework\Exception\NoSuchEntityException;

class Price implements \Ced\Integrator\Helper\Product\PriceInterface
{
    /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search */
    public $search;

    /** @var \Magento\Framework\Api\FilterFactory */
    public $filter;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    public $products;

    /** @var \Ced\Amazon\Api\ProfileRepositoryInterface */
    public $profile;

    /**
     * @var \Ced\Amazon\Api\QueueRepositoryInterface
     */
    public $queue;

    /** @var \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory */
    public $queueDataFactory;

    /** @var \Ced\Amazon\Api\AccountRepositoryInterface */
    public $account;

    /** @var \Ced\Amazon\Api\FeedRepositoryInterface */
    public $feed;

    /** @var \Ced\Amazon\Helper\Config */
    public $config;

    /** @var \Ced\Amazon\Helper\Logger */
    public $logger;

    /** @var \Amazon\Sdk\EnvelopeFactory */
    public $envelope;

    /** @var \Amazon\Sdk\Product\PriceFactory */
    public $price;
    /**
     * @var \Ced\Amazon\Service\Config
     */
    public $configuration;

    /**
     * @var \Amazon\Sdk\Marketplace
     */
    public $marketplace;

    /**'
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    public $currencyDirectory;

    /** @var \Magento\Catalog\Model\Product  */
    public $productData;

    public function __construct(
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search,
        \Magento\Framework\Api\FilterFactory $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productsFactory,
        \Magento\Catalog\Model\Product $productData,
        \Ced\Amazon\Api\AccountRepositoryInterface $account,
        \Ced\Amazon\Api\ProfileRepositoryInterface $profile,
        \Ced\Amazon\Api\QueueRepositoryInterface $queue,
        \Ced\Amazon\Api\FeedRepositoryInterface $feed,
        \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory $queueDataFactory,
        \Ced\Amazon\Helper\Config $config,
        \Ced\Amazon\Helper\Logger $logger,
        \Amazon\Sdk\EnvelopeFactory $envelopeFactory,
        \Amazon\Sdk\Product\PriceFactory $price,
        \Ced\Amazon\Service\Config $configuration,
        \Amazon\Sdk\Marketplace $marketplace,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Helper\Data $currencyDirectory
    ) {
        $this->search = $search;
        $this->filter = $filter;
        $this->configuration = $configuration;

        $this->products = $productsFactory;
        $this->productData = $productData;

        $this->account = $account;
        $this->profile = $profile;
        $this->feed = $feed;
        $this->queue = $queue;
        $this->queueDataFactory = $queueDataFactory;
        $this->logger = $logger;
        $this->config = $config;

        $this->envelope = $envelopeFactory;
        $this->price = $price;
        $this->marketplace = $marketplace;
        $this->storeManager = $storeManager;
        $this->currencyDirectory = $currencyDirectory;
    }

    /**
     * @TODO: Update price for 'uploaded' products only,
     * Update the values for provided ids
     * @param array $ids
     * @param bool $throttle
     * @param string $priority
     * @return boolean
     * @throws \Exception
     */
    public function update(
        array $ids = [],
        $throttle = true,
        $priority = \Ced\Amazon\Model\Source\Queue\Priorty::MEDIUM
    ) {
        $status = false;
        if (isset($ids) && !empty($ids)) {
            $profileIds = $this->profile->getProfileIdsByProductIds($ids);
            if (!empty($profileIds)) {
                /** @var \Magento\Framework\Api\Filter $idsFilter */
                $idsFilter = $this->filter->create();
                $idsFilter->setField(\Ced\Amazon\Model\Profile::COLUMN_ID)
                    ->setConditionType('in')
                    ->setValue($profileIds);

                /** @var \Magento\Framework\Api\Filter $statusFilter */
                $statusFilter = $this->filter->create();
                $statusFilter->setField(\Ced\Amazon\Model\Profile::COLUMN_STATUS)
                    ->setConditionType('eq')
                    ->setValue(\Ced\Amazon\Model\Source\Profile\Status::ENABLED);

                /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteria */
                $criteria = $this->search->create();
                $criteria->addFilter($statusFilter);
                $criteria->addFilter($idsFilter);
                /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface $profiles */
                $profiles = $this->profile->getList($criteria->create());
                /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $accounts */
                $accounts = $profiles->getAccounts();

                /** @var array $stores */
                $stores = $profiles->getProfileByStoreIdWise();

                /** @var string $type */
                $type = $this->config->getPriceType();

                /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                foreach ($accounts->getItems() as $accountId => $account) {
                    foreach ($stores as $storeId => $profiles) {
                        $envelope = null;
                        $profiles = array_filter($profiles, function ($profile) use ($accountId) {
                            return $profile->getAccountId() == $accountId;
                        });
                        /** @var \Ced\Amazon\Api\Data\ProfileInterface $profile */
                        foreach ($profiles as $profileId => $profile) {
                            /** @var array $productIds */
                            $productIds = $this->profile->getAssociatedProductIds($profileId, $storeId, $ids);
                            /** @var array $marketplaceIds */
                            $marketplaceIds = $profile->getMarketplaceIds();

                            if (!empty($productIds)) {
                                if ($throttle == true) {
                                    $individual = $this->config->sendPriceFeedMPWise();
                                    // queue
                                    if ($type == \Ced\Amazon\Model\Source\Config\Price::TYPE_ATTRIBUTE || $individual) {
                                        // 1. Different price for different countries
                                        foreach ($marketplaceIds as $marketplaceId) {
                                            $status = $this->push($profile, $marketplaceId, $productIds, $priority);
                                        }
                                    } else {
                                        // 2. Same price for different countries
                                        $status = $this->push(
                                            $profile,
                                            $profile->getMarketplace(),
                                            $productIds,
                                            $priority
                                        );
                                    }
                                } else {
                                    //TODO: add all data to uniqueid in session & process via multiple ajax requests.
                                    $individual = $this->config->sendPriceFeedMPWise();
                                    // prepare & send: divide in chunks and process in multiple requests
                                    if ($type == \Ced\Amazon\Model\Source\Config\Price::TYPE_ATTRIBUTE || $individual) {
                                        // 1. Different price for different countries
                                        foreach ($marketplaceIds as $marketplaceId) {
                                            // 2. Same price for different countries
                                            $specifics = [
                                                'ids' => $productIds,
                                                'account_id' => $accountId,
                                                'marketplace' => $marketplaceId,
                                                'profile_id' => $profileId,
                                                'store_id' => $storeId,
                                                'type' => \Amazon\Sdk\Api\Feed::PRODUCT_PRICING,
                                            ];
                                            // New Envelope is used for each feed.
                                            $envelope = $this->prepare($specifics, null);
                                            $this->feed->send($envelope, $specifics);
                                            $status = true;
                                        }
                                    } else {
                                        // 2. Same price for different countries
                                        $specifics = [
                                            'ids' => $productIds,
                                            'account_id' => $accountId,
                                            'marketplace' => $profile->getMarketplace(),
                                            'profile_id' => $profileId,
                                            'store_id' => $storeId,
                                            'type' => \Amazon\Sdk\Api\Feed::PRODUCT_PRICING,
                                        ];

                                        $envelope = $this->prepare($specifics, $envelope);
                                        $this->feed->send($envelope, $specifics);
                                        $status = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $status;
    }

    /**
     * Push item in queue
     * @param \Ced\Amazon\Api\Data\ProfileInterface $profile
     * @param string $marketplace
     * @param array $ids
     * @param string $priority
     * @return bool
     */
    private function push(
        $profile,
        $marketplace,
        $ids,
        $priority = \Ced\Amazon\Model\Source\Queue\Priorty::MEDIUM
    ) {
        $status = false;
        $productIds = [];
        try {
            // queue
            $productIds = $this->profile
                ->getAssociatedProductIds($profile->getId(), $profile->getStoreId(), $ids);
            $specifics = [
                'ids' => $productIds,
                'account_id' => $profile->getAccountId(),
                'marketplace' => $marketplace,
                'profile_id' => $profile->getId(),
                'store_id' => $profile->getStoreId(),
                'type' => \Amazon\Sdk\Api\Feed::PRODUCT_PRICING,
            ];
            /** @var \Ced\Amazon\Api\Data\Queue\DataInterface $queueData */
            $queueData = $this->queueDataFactory->create();
            $queueData->setAccountId($profile->getAccountId());
            $queueData->setMarketplace($marketplace);
            $queueData->setSpecifics($specifics);
            $queueData->setType(\Amazon\Sdk\Api\Feed::PRODUCT_PRICING);
            $queueData->setPriorty($priority);
            $status = $this->queue->push($queueData);
        } catch (\Exception $e) {
            $this->logger->error(
                "Amazon Cron : All price failed.",
                [
                    'status' => $status,
                    'count' => count($productIds),
                    'exception' => $e->getMessage()
                ]
            );
        }
        return $status;
    }

    /**
     * Prepare Price for Amazon
     * @param array $specifics
     * @param array|null $envelope
     * @return \Amazon\Sdk\Envelope|null
     * @throws \Exception
     */
    public function prepare(array $specifics = [], $envelope = null)
    {
        if (isset($specifics) && !empty($specifics)) {
            $sale = $this->config->getAllowSalePrice();

            $ids = $specifics['ids'];
            /** @var \Ced\Amazon\Api\Data\ProfileInterface $profile */
            $profile = $this->profile->getById($specifics['profile_id']);

            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            $store = $profile->getStore();

//          $currency='DEFAULT';
            $currency = $this->marketplace->getCurrencyCodeByMarketplaceId($specifics['marketplace']);

            /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
            $account = $this->account->getById($specifics['account_id']);

            if (!isset($envelope)) {
                /** @var \Amazon\Sdk\Envelope $envelope */
                $envelope = $this->envelope->create(
                    [
                        'merchantIdentifier' => $account->getConfig($profile->getMarketplaceIds())->getSellerId(),
                        'messageType' => \Amazon\Sdk\Base::MESSAGE_TYPE_PRICE
                    ]
                );
            }

            $attributeList = $this->config->getPriceAttributeList();
            $attributeList = array_merge(
                $attributeList,
                ['sku', 'entity_id', 'type_id', 'price', 'special_price', 'special_from_date', 'special_to_date']
            );
            $attributeSku = $this->config->getPriceAlternateSku();
            if ($attributeSku) {
                $attributeSku = explode(",", $attributeSku);
            }
            if (empty($attributeSku)) {
                $attributeSkuList = ["sku"];
            } else {
                $attributeSkuList = $attributeSku;
            }

            /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $products */
            $products = $this->products->create()
                ->setStoreId($store->getId())
                ->addAttributeToSelect($attributeList)
                ->addAttributeToFilter('entity_id', ['in' => $ids]);
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($products as $product) {
                // case 1 : for configurable products
                if ($product->getTypeId() == 'configurable') {
                    $parentId = $product->getId();
                    $productType = $product->getTypeInstance();

                    /** @codingStandardsIgnoreStart */
                    $childIds = $productType->getChildrenIds($parentId);
                    /** @codingStandardsIgnoreEnd */
                    /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $childs */
                    $childs = $this->products->create()
                        ->setStoreId($store->getId())
                        ->addAttributeToSelect($attributeList)
                        ->addAttributeToFilter('entity_id', ['in' => $childIds[0]]);
                    /** @var \Magento\Catalog\Model\Product $child */
                    foreach ($childs as $child) {
                        /** @var array $value */
                        $value = $this->calculate($child, $specifics);
                        foreach ($attributeSkuList as $key=>$attr) {
                            $productAttr = $this->productData->load($child->getId());
                            if ($attr == 'default_value') {
                                $attr = 'sku';
                            }
                            if ($productAttr->getData($attr)) {
                                /** @var \Amazon\Sdk\Product\Price $price */
                                $price = $this->price->create();
                                $price->setId($child->getId() . $key);
                                $price->setData($productAttr->getData($attr), $value['SalePrice'], $currency);

                                if ($sale) {
                                    $price->setData($productAttr->getData($attr), $value['StandardPrice'], $currency);
                                    $from = $value['StartDate'];
                                    $to = $value['EndDate'];
                                    $price->setSale($value['SalePrice'], $from, $to, $currency);
                                }

                                if (!empty($value['BusinessPrice'])) {
                                    $price->setBusinessPrice($value['BusinessPrice']);
                                }
                                if ($specifics['marketplace']=='A1RKKUPIHCS9HS'
                                    || $specifics['marketplace']=='A13V1IB3VIYZZH'
                                    || $specifics['marketplace']=='A1PA6795UKMFR9'
                                    || $specifics['marketplace']=='APJ6JRA9NG5V4'||
                                    $specifics['marketplace']=='A1805IZSGTT6HS'&& $currency="EUR"
                                ) {
                                    $value['MinimumPrice'] = str_replace('.', ',', $value['MinimumPrice']);
                                }
                                $price->setMinSellerAllowedPrice($value['MinimumPrice'], $currency);

                                $envelope->addPrice($price);
                            }
                        }
                    }
                } elseif ($product->getTypeId() == 'simple' || $product->getTypeId() == 'bundle') {
                    // case 2 : for simple products

                    /** @var array $value */
                    $value = $this->calculate($product, $specifics);
                    foreach ($attributeSkuList as $key=>$attr) {
                        $productAttr = $this->productData->load($product->getId());
                        if ($attr == 'default_value') {
                            $attr = 'sku';
                        }
                        if ($productAttr->getData($attr)) {
                            /** @var \Amazon\Sdk\Product\Price $price */
                            $price = $this->price->create();
                            $price->setId($product->getId() . $key);
                            $price->setData($productAttr->getData($attr), $value['SalePrice'], $currency);

                            if ($sale) {
                                $price->setData($productAttr->getData($attr), $value['StandardPrice'], $currency);
                                $from = $value['StartDate'];
                                $to = $value['EndDate'];
                                $price->setSale($value['SalePrice'], $from, $to, $currency);
                            }
                            if (!empty($value['BusinessPrice'])) {
                                $price->setBusinessPrice($value['BusinessPrice']);
                            }
                            if ($specifics['marketplace']=='A1RKKUPIHCS9HS'
                                || $specifics['marketplace']=='A13V1IB3VIYZZH'
                                || $specifics['marketplace']=='A1PA6795UKMFR9'
                                || $specifics['marketplace']=='APJ6JRA9NG5V4'||
                                $specifics['marketplace']=='A1805IZSGTT6HS'&& $currency="EUR"
                            ) {
                                $value['MinimumPrice'] = str_replace('.', ',', $value['MinimumPrice']);
                            }
                            $price->setMinSellerAllowedPrice($value['MinimumPrice'], $currency);
                            $envelope->addPrice($price);
                        }
                    }
                }
            }
        }
        return $envelope;
    }

    /**
     * TODO: add number formating for comma and dot separators.
     * Calculate price on the basis of global config
     * @param \Magento\Catalog\Model\Product $product
     * @param array $specifics
     * @return array
     * @throws NoSuchEntityException
     */
    public function calculate($product, $specifics)
    {

        // Overridding sale price from attribute mapping
        $useDefault = $this->config->useDefaultSalePrice();
        $salePriceAttribute = $this->config->getSalePriceAttribute();
        if (!$useDefault) {
            $splprice = (float)str_replace(',', '', trim((string)$product->getData($salePriceAttribute)));
            $start = $this->config->getSalePriceStartDate();
            $end = $this->config->getSalePriceEndDate();
        } else {
            // FinalPrice is always the lower of special_price and price.
            $splprice = (float)str_replace(',', '', $product->getFinalPrice());
            $start = $product->getData('special_from_date');
            $end = $product->getData('special_to_date');
        }

        $minimumPriceAttribute = $this->config->getMinimumPriceAttribute();
        if ($minimumPriceAttribute == 'default_value') {
            $minimumPriceAttribute = 'price';
        }

        $minimumPrice = (float)str_replace(',', '', trim((string)$product->getData($minimumPriceAttribute)));

        $businessPrice = null;
        if ($this->config->getAllowedBusinessPrice()) {
            $businessPriceAttribute = $this->config->getBusinessPriceAttribute();
            if ($businessPriceAttribute == 'default_value') {
                $businessPriceAttribute = 'price';
            }
            $businessPrice = (float)str_replace(',', '', trim((string)$product->getData($businessPriceAttribute)));
        }

        $price = (float)str_replace(',', '', $product->getPrice());
        $type = $this->config->getPriceType();
        switch ($type) {
            case \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_INCREASE:
                $fixed = $this->config->getPriceFixed();
                $splprice = $this->calculateFixed(
                    $splprice,
                    $fixed,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_INCREASE
                );
                $price = $this->calculateFixed(
                    $price,
                    $fixed,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_INCREASE
                );
                break;

            case \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_DECREASE:
                $fixed = $this->config->getPriceFixed();
                $price = $this->calculateFixed(
                    $price,
                    $fixed,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_DECREASE
                );
                $splprice = $this->calculateFixed(
                    $splprice,
                    $fixed,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_DECREASE
                );
                break;

            case \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_INCREASE:
                $percentage = $this->config->getPricePercentage();
                $price = $this->calculatePercentage(
                    $price,
                    $percentage,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_INCREASE
                );
                $splprice = $this->calculatePercentage(
                    $splprice,
                    $percentage,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_INCREASE
                );
                break;

            case \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_DECREASE:
                $percentage = $this->config->getPricePercentage();
                $price = $this->calculatePercentage(
                    $price,
                    $percentage,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_DECREASE
                );
                $splprice = $this->calculatePercentage(
                    $splprice,
                    $percentage,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_DECREASE
                );
                break;

            case \Ced\Amazon\Model\Source\Config\Price::TYPE_ATTRIBUTE:
                $mappings = $this->config->getPriceAttribute();
                $marketplaceIds = isset($specifics['marketplace']) ?
                    explode(',', $specifics['marketplace']) : [];
                try {
                    foreach ($marketplaceIds as $marketplaceId) {
                        if (isset($mappings[$marketplaceId]) && !empty($mappings[$marketplaceId])) {
                            $custom = (float)str_replace(',', '', $product->getData($mappings[$marketplaceId]));
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
                }

                $price = (empty($custom) || $custom == 0.00) ? $price : $custom;
                if (!$this->config->getAllowSalePrice()) {
                    // Different price when sale price is enabled. TODO: Review.
                    $splprice = $price;
                }

                break;
        }

        $splprice = round((float)$splprice, 2);
        $price = round((float)$price, 2);

        if ((empty($splprice) || $splprice == 0.00) && (!empty($price) || $price != 0.00)) {
            $splprice = $price;
        }

        if ((empty($price) || $price == 0.00) && (!empty($splprice) || $splprice != 0.00)) {
            $price = $splprice;
        }

        // StandardPrice > SalePrice in Amazon
        $response = [
            'StandardPrice' => (string)$price,
            'SalePrice' => (string)$splprice,
            'StartDate' => $start,
            'EndDate' => $end,
            'MinimumPrice' => $minimumPrice,
            'BusinessPrice' => $businessPrice
        ];

        if ($splprice < $price) {
            $response = [
                'StandardPrice' => (string)$price,
                'SalePrice' => (string)$splprice,
                'StartDate' => $start,
                'EndDate' => $end,
                'MinimumPrice' => $minimumPrice,
                'BusinessPrice' => $businessPrice
            ];
        } elseif ($price < $splprice) {
            $response = [
                'StandardPrice' => (string)$splprice,
                'SalePrice' => (string)$price,
                'StartDate' => $start,
                'EndDate' => $end,
                'MinimumPrice' => $minimumPrice,
                'BusinessPrice' => $businessPrice
            ];
        }

        // check if priceConversionMarketPlaceWise is Enable
        if ($this->config->currencyConversionMarketplaceWise()) {
            $response = $this->priceConversionMarketplaceWise($response, $specifics);
        }

        return $response;
    }

    /**
     * ForFixPrice
     * @param null $price
     * @param null $fixed
     * @param string $type
     * @return float|null
     */
    public function calculateFixed(
        $price = null,
        $fixed = null,
        $type = \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_DECREASE
    ) {
        if (is_numeric($fixed) && ($fixed != '')) {
            $fixed = (float)$fixed;
            if ($fixed > 0) {
                $price = $type == \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_DECREASE ?
                    (float)($price + $fixed) : (float)($price - $fixed);
            }
        }
        return $price;
    }

    /**
     * ForPerPrice
     * @param null $price
     * @param null $percentage
     * @param string $type
     * @return float|null
     */
    public function calculatePercentage(
        $price = null,
        $percentage = null,
        $type = \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_INCREASE
    ) {
        if (is_numeric($percentage)) {
            $percentage = (float)$percentage;
            if ($percentage > 0) {
                $price = $type == \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_INCREASE ?
                    (float)($price + (($price / 100) * $percentage))
                    : (float)($price - (($price / 100) * $percentage));
            }
        }
        return $price;
    }

    /**
     * @param array $response
     * @param $specifics
     * @return array
     * @throws NoSuchEntityException
     */
    public function priceConversionMarketplaceWise(array $response, $specifics)
    {
        $standardPrice = $response['StandardPrice'];
        $salePrice = $response['SalePrice'];
        $start = $response['StartDate'];
        $end = $response['EndDate'];
        $marketplaceId = $specifics['marketplace'];
        $marketplaceCurrencyCode = $this->marketplace->getCurrencyCodeByMarketplaceId($marketplaceId);
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        $convertedStandardPrice =round($this->currencyDirectory
            ->currencyConvert($standardPrice, $baseCurrencyCode, $marketplaceCurrencyCode), 2);
        $convertedSalePrice =round($this->currencyDirectory
            ->currencyConvert($salePrice, $baseCurrencyCode, $marketplaceCurrencyCode), 2);
        return $response = [
            'StandardPrice' => $convertedStandardPrice,
            'SalePrice' => $convertedSalePrice,
            'StartDate' => $start,
            'EndDate' => $end
        ];
    }
}
