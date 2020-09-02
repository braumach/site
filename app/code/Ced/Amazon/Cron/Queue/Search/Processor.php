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

namespace Ced\Amazon\Cron\Queue\Search;

use Amazon\Sdk\Api\Product\ProductListFactory;
use Ced\Amazon\Api\AccountRepositoryInterface;
use Ced\Amazon\Api\Data\ProfileInterface;
use Ced\Amazon\Api\Data\QueueInterface;
use Ced\Amazon\Api\Data\Search\ProductInterfaceFactory;
use Ced\Amazon\Api\Data\Strategy\AttributeInterface;
use Ced\Amazon\Api\ProfileRepositoryInterface;
use Ced\Amazon\Api\QueueRepositoryInterface;
use Ced\Amazon\Api\StrategyRepositoryInterface;
use Ced\Amazon\Model\ResourceModel\Profile\Product\CollectionFactory as AmazonProfileProductCollectionFactory;
use Ced\Amazon\Model\ResourceModel\Search\Product as AmazonSearchProductResource;
use Magento\Catalog\Model\ResourceModel\Product as CatalogProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as MagentoCatalogProductCollectionFactory;
use Magento\Framework\Api\FilterFactory;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class Processor extends \Ced\Amazon\Cron\Queue\Processor\Base
{
    const PRODUCT_SEARCH = '_POST_PRODUCT_DATA_SEARCH_';

    /** @var ProfileRepositoryInterface $profileRepository */
    public $profileRepository;

    /** @var string $cacheIdentifier */
    public $cacheIdentifier = "search_processor_cron_status";

    public $result;

    /** @var StrategyRepositoryInterface $strategyRepository */
    public $strategyRepository;

    /** @var AttributeInterface */
    public $strategyAttribute;

    /** @var $attributeList */
    public $attributeList;

    /** @var $strategyMapAttribute */
    public $strategyMapAttribute = [];

    /** @var \Ced\Amazon\Helper\Product $product */
    public $amazonProductService;

    /** @var \Ced\Amazon\Model\ResourceModel\Search\Product */
    public $searchResourceModel;

    /** @var ProductInterfaceFactory $searchProductFactory */
    public $searchProductFactory;

    /** @var \Ced\Amazon\Model\ResourceModel\Profile\Product\CollectionFactory $profileProductCollectionFactory */
    public $profileProductCollectionFactory;

    /** @var CollectionFactory */
    public $productCollectionFactory;

    /** @var \Ced\Amazon\Api\Data\ProductInterface $amznProductInterface */
    public $amznProductInterface;

    /** @var CatalogProductResource $productResource */
    public $productResource;

    /** @var \Ced\Amazon\Model\ResourceModel\Product $productResourceModel */
    public $productResourceModel;

    /** @var \Magento\Catalog\Model\Product $products */
    public $products;

    /** @var ProductListFactory $productList */
    public $productList;

    /** @var AccountRepositoryInterface $account , */
    public $account;

    /** @var \Ced\Amazon\Repository\Profile\Product $profileProduct */
    public $profileProduct;

//    public $amazon_profile_product_relation_id;

    /**
     * Matching Rules
     * @var array
     */
    public $rules = [];

    /**
     * Intial Cron Status : Start with listing search
     */
    public $init = [
        self::PRODUCT_SEARCH => true
    ];

    public $status = [
        self::PRODUCT_SEARCH => true
    ];
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    public function __construct(
        array $rules = [],
        DateTime $dateTime,
        FilterFactory $filter,
        \Ced\Amazon\Model\Cache $cache,
        QueueRepositoryInterface $queue,
        SerializerInterface $serializer,
        ProductListFactory $productList,
        \Ced\Amazon\Helper\Logger $logger,
        \Ced\Amazon\Service\Config $config,
        AccountRepositoryInterface $account,
        \Ced\Amazon\Helper\Product $product,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilderFactory $search,
        AttributeInterface $strategyAttribute,
        CatalogProductResource $productResource,
        \Magento\Catalog\Model\Product $products,
        ProfileRepositoryInterface $profileRepository,
        ProductInterfaceFactory $searchProductFactory,
        StrategyRepositoryInterface $strategyRepository,
        AmazonSearchProductResource $searchResourceModel,
        \Ced\Amazon\Repository\Profile\Product $profileProduct,
        \Ced\Amazon\Model\ResourceModel\Product $productResourceModel,
        MagentoCatalogProductCollectionFactory $productCollectionFactory,
        \Ced\Amazon\Api\Data\ProductInterfaceFactory $amznProductInterface,
        AmazonProfileProductCollectionFactory $profileProductCollectionFactory
    ) {
        parent::__construct($dateTime, $serializer, $search, $filter, $queue, $cache, $config, $logger);
        $this->rules = $rules;
        $this->storeManager = $storeManager;
        $this->productResource = $productResource;
        $this->profileRepository = $profileRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->strategyRepository = $strategyRepository;
        $this->strategyAttribute = $strategyAttribute;
        $this->amazonProductService = $product;
        $this->searchProductFactory = $searchProductFactory;
        $this->searchResourceModel = $searchResourceModel;
        $this->profileProductCollectionFactory = $profileProductCollectionFactory;
        $this->amznProductInterface = $amznProductInterface;
        $this->productResourceModel = $productResourceModel;
        $this->products = $products;
        $this->productList = $productList;
        $this->account = $account;
        $this->profileProduct = $profileProduct;
    }

    public function execute()
    {
        try {
            // Get the status of the next staged action.
            $this->type = self::PRODUCT_SEARCH;

            // Setting the default operation type to 'REQUEST' or 'GET'.
            $operationType = null; // setting null for random processing.

            $this->logger->info(
                self::LOGGING_TAG . "execution started.",
                ['type' => $this->type, 'processor' => self::PROCESSOR_TYPE]
            );

            /** @var QueueInterface $item */
            $item = $this->queue->pop(
                $this->type,
                [
                    \Ced\Amazon\Model\Source\Queue\Status::SUBMITTED,
                    \Ced\Amazon\Model\Source\Queue\Status::PROCESSED
                ],
                $operationType
            );

            $list = $this->getList($item);

            // If current set operation have queue records, then process them.
            if ($list->getTotalCount() > 0) {
                /** @var QueueInterface[] $items */
                $items = $list->getItems();
                $this->process($items);
            } else {
                $this->status[$this->type] = false;

                $this->logger->info(
                    self::LOGGING_TAG . "current status set to false.",
                    ['type' => $this->type, 'status' => $this->status, 'processor' => self::PROCESSOR_TYPE]
                );

                foreach ($this->status as $type => $value) {
                    $this->type = $type;

                    $operationType = \Amazon\Sdk\Base::OPERATION_TYPE_REQUEST;

                    /** @var QueueInterface $item */
                    $item = $this->queue->pop(
                        $this->type,
                        \Ced\Amazon\Model\Source\Queue\Status::SUBMITTED,
                        $operationType
                    );

                    // Getting the queue records for current feed type
                    $list = $this->getList($item);

                    if ($list->getTotalCount() > 0) {
                        /** @var QueueInterface[] $items */
                        $items = $list->getItems();
                        $this->process($items);
                        break;
                    } else {
                        $this->stageNext();
                        continue;
                    }
                }
            }
            return true;
        } catch (\Exception $exception) {
            $this->logger->error(
                self::LOGGING_TAG . "failed.",
                [
                    'path' => __METHOD__,
                    'message' => $exception->getMessage(),
                    'exception' => $exception->getTraceAsString(),
                    'processor' => self::PROCESSOR_TYPE
                ]
            );
            return false;
        }
    }

    private function getList($item)
    {
        /** @var \Magento\Framework\Api\Filter $statusFilter */
        $statusFilter = $this->filter->create();
        $statusFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_STATUS)
            ->setConditionType('eq')
            ->setValue($item->getStatus());

        /** @var \Magento\Framework\Api\Filter $typeFilter */
        $typeFilter = $this->filter->create();
        $typeFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_TYPE)
            ->setConditionType('eq')
            ->setValue($item->getType());

        /** @var \Magento\Framework\Api\Filter $operationTypeFilter */
        $operationTypeFilter = $this->filter->create();
        $operationTypeFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_OPERATION_TYPE)
            ->setConditionType('eq')
            ->setValue($item->getOperationType());

        /** @var \Magento\Framework\Api\Filter $marketplaceFilter */
        $marketplaceFilter = $this->filter->create();
        $marketplaceFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_MARKETPLACE)
            ->setConditionType('eq')
            ->setValue($item->getMarketplace());

        /** @var \Magento\Framework\Api\Filter $accountIdFilter */
        $accountIdFilter = $this->filter->create();
        $accountIdFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_ACCOUNT_ID)
            ->setConditionType('eq')
            ->setValue($item->getAccountId());

        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteria */
        $criteria = $this->search->create();
        $criteria->addFilter($statusFilter);
        $criteria->addFilter($typeFilter);
        $criteria->addFilter($operationTypeFilter);
        $criteria->addFilter($marketplaceFilter);
        $criteria->addFilter($accountIdFilter);

        // static size setting for getting report case
        if ($item->getOperationType() == \Amazon\Sdk\Base::OPERATION_TYPE_GET) {
            $criteria->setPageSize(5);
            $criteria->setCurrentPage(1);
        }

        // Getting the queue records for current feed type.
        /** @var \Ced\Amazon\Api\Data\QueueSearchResultsInterface $list */
        $list = $this->queue->getList($criteria->create());

        return $list;
    }

    public function process(array $items)
    {
        $itemIds = [];
        $envelope = null;
        $requestId = null;
        $requested = false;
        $specifics = null;
        /** @var QueueInterface $item */
        foreach ($items as $i => $item) {
            try {
                $specifics = $item->getSpecifics();
                if ($item->getOperationType() == \Amazon\Sdk\Base::OPERATION_TYPE_UPDATE) {
                    if ($requested == false) {
                        $requestId = $this->update($specifics);
                        if ($requestId !== false) {
                            $specifics['request_id'] = \Ced\Amazon\Model\Source\Feed\Status::DONE;
                            $requested = true;
                        }
                    }

                    if ($requested) {
                        $item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::PROCESSED);
                        $specifics['request_id'] = $requestId;
                        $item->setSpecifics($this->serializer->serialize($specifics));
                        $item->setOperationType(\Amazon\Sdk\Base::OPERATION_TYPE_GET);
                    }
                }

                if ($item->getOperationType() == \Amazon\Sdk\Base::OPERATION_TYPE_GET) {
                    $status = $specifics['request_id'];
                    if ($status == \Ced\Amazon\Model\Source\Feed\Status::DONE) {
                        $item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::DONE);
                    }
                }
                $item->setExecutedAt($this->dateTime->gmtDate());
            } catch (\Exception $e) {
                $item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::ERROR);
                $this->logger->error(
                    $e->getMessage(),
                    [
                        'path' => __METHOD__,
                        'response' => $specifics,
                        'type' => $this->type,
                        'queue_ids' => $itemIds,
                        'processor' => self::PROCESSOR_TYPE
                    ]
                );
            }

            $this->queue->save($item);
        }

        $this->logger->debug(
            self::LOGGING_TAG . "feed processed.",
            [
                'path' => __METHOD__,
                'response' => $specifics,
                'type' => $this->type,
                'queue_ids' => $itemIds,
                'processor' => self::PROCESSOR_TYPE
            ]
        );

        $this->stageNext();
    }

    /**
     * @param $specifics
     * @return bool
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function update($specifics)
    {
        if ($specifics['type'] == Processor::PRODUCT_SEARCH) {
            $collection = $this->productCollectionFactory->create();

            if (isset($specifics['ids']) && (!empty($specifics['ids']) && $specifics['ids'] != ['*'])) {
                $collection->addFieldToFilter('entity_id', ['in' => $specifics['ids']]);
            }

            $ids = $collection->getColumnValues('entity_id');
            /** @var \Ced\Amazon\Repository\Profile\SearchResults $profiles */
            $profiles = $this->profileRepository->getProfilesByProductIds($ids);

            /** @var \Ced\Amazon\Model\Profile $profile */
            foreach ($profiles->getItems() as $profile) {
                try {
                    $profileProductIds = $this->profileRepository
                        ->getAssociatedProductIds($profile->getId(), $profile->getStoreId(), $ids);

                    $strategyId = $profile->getStrategyAttribute();
                    $typeObject = $this->strategyRepository->getById($strategyId, true)->getTypeObject();
                    if ($typeObject instanceof \Ced\Amazon\Api\Data\Strategy\AttributeInterface) {
                        $this->strategyMapAttribute = $typeObject->getAttributeMapping();
                    }

                    /** @var Collection $products */
                    $products = $this->getCollection($profile, $profileProductIds);
                    foreach ($products as $product) {
                        $this->search($product, $profile);
                    }
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }
        return true;
    }

    /**
     * Loading Product Collection
     * @param ProfileInterface $profile
     * @param array $ids
     * @return Collection
     */
    private function getCollection($profile, $ids)
    {
        $this->getAttributeList();
        $collection = $this->productCollectionFactory->create()
            ->setStoreId($profile->getStoreId())
            ->addAttributeToSelect($this->attributeList)
            ->addAttributeToFilter('entity_id', ['in' => $ids]);

        $collection->getSelect()->join(
            ['capp' => \Ced\Amazon\Model\Profile\Product::NAME],
            "e.entity_id = capp.product_id",
            'capp.id as capp_relation_id'
        )->where('capp.profile_id=?', $profile->getId());

        return $collection;
    }

    /**
     * Create a Global Array for Strategy Attribute List
     */
    private function getAttributeList()
    {
        foreach ($this->strategyMapAttribute as $strategyAttributeKey => $strategyAttributeValue) {
            $this->attributeList[] = $strategyAttributeValue;
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Ced\Amazon\Model\Profile $profile
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function search($product, $profile)
    {
        $searchResponse = [];
        $valid = false;

        $identifiers = [
            AttributeInterface::COLUMN_ASIN,
            AttributeInterface::COLUMN_EAN,
            AttributeInterface::COLUMN_UPC,
            AttributeInterface::COLUMN_GTIN,
        ];

        $productIdentifier = false;
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $childIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            $this->profileProduct->addProductsIdsWithProfileId(
                $childIds[0],
                $profile->getId()
            );
            if (isset($childIds[0])) {
                foreach ($childIds[0] as $childId) {
                    $this->productResource->load($this->products, $childId, 'id');

                    foreach ($identifiers as $identifier) {
                        $searchResponse = $this->load(
                            $profile,
                            $this->products->getData($this->strategyMapAttribute[$identifier]),
                            strtoupper($identifier)
                        );
                        if (count($searchResponse['product']) > 0) {
                            $valid = true;
                            $productIdentifier = $identifier;
                            break;
                        }
                    }
                }
            }
        } else {
            $this->productResource->load($this->products, $product->getId(), 'id');
            foreach ($identifiers as $identifier) {
                $searchResponse = $this->load(
                    $profile,
                    $product->getData($this->strategyMapAttribute[$identifier]),
                    strtoupper($identifier)
                );
                if (count($searchResponse['product']) > 0) {
                    $valid = true;
                    $productIdentifier = $identifier;
                    break;
                }
            }
        }

        if ($valid) {
            /** @var \Ced\Amazon\Api\Data\Search\ProductInterface $search */
            $search = $this->searchProductFactory->create();
            if (isset($searchResponse['product']['Identifiers']['MarketplaceASIN']['ASIN'])) {
                $search->setAsin($searchResponse['product']['Identifiers']['MarketplaceASIN']['ASIN']);
                $this->searchResourceModel->load(
                    $search,
                    $search->getAsin(),
                    \Ced\Amazon\Api\Data\Search\ProductInterface::COLUMN_ASIN
                );

                if (!$search->getId() ||
                    $search->getMarketplaceId() ==
                    $searchResponse['product']['Identifiers']['MarketplaceASIN']['MarketplaceId']) {
                    $search = $this->searchProductFactory->create();
                    $search->setAsin($searchResponse['product']['Identifiers']['MarketplaceASIN']['ASIN']);
                    $search->
                    setMarketplaceId($searchResponse['product']['Identifiers']['MarketplaceASIN']['MarketplaceId']);
                }
            }

            if (isset($searchResponse['product']['AttributeSets'])) {
                foreach ($searchResponse['product']['AttributeSets'] as $attributeSet) {
                    $search->setRelationId($product['capp_relation_id']);
                    $search->setBrand($attributeSet['Brand']);
                    $search->setModel($attributeSet['Model']);
                    $search->setTitle($attributeSet['Title']);
                    $search->setManufacturer($attributeSet['Manufacturer']);
                    $search->setDescription($product['description']);
                    $search->setImage($attributeSet['SmallImage']['URL']);
                    $search->setResponse($this->serializer->serialize($searchResponse));
                    $search->
                    setMarketplaceId($searchResponse['product']['Identifiers']['MarketplaceASIN']['MarketplaceId']);
                    $search->setIdentifier(
                        $productIdentifier,
                        $product[$this->strategyMapAttribute[$productIdentifier]]
                    );
                }
                $this->searchResourceModel->save($search);

                $result = $this->match($product, $search);

                // Saving Data On 'ced_amazon_product' table if Matched criteria more than 2
                if (count($result) >= 2) {
                    /** @var \Ced\Amazon\Api\Data\ProductInterface $amznProductInterface */
                    $amznProductInterface = $this->amznProductInterface->create();
                    $amznProductInterface->setAsin($search->getAsin());
                    $this->productResourceModel->load(
                        $amznProductInterface,
                        $amznProductInterface->getAsin(),
                        \Ced\Amazon\Model\Product::COLUMN_ASIN
                    );

                    if (!$amznProductInterface->getId() ||
                        $amznProductInterface->getMarketplaceId() ==
                        $searchResponse['product']['Identifiers']['MarketplaceASIN']['MarketplaceId']) {
                        $amznProductInterface = $this->amznProductInterface->create();
                        $amznProductInterface->setAsin($search->getAsin());
                    }
                    $amznProductInterface->setRelationId($search->getRelationId());
                    $amznProductInterface->setMarketplaceId(
                        $searchResponse['product']['Identifiers']['MarketplaceASIN']['MarketplaceId']
                    );
                    $amznProductInterface->setStatus(null);
                    $amznProductInterface->setErrors(null);
                    $amznProductInterface->
                    setTitleFlag(isset($result['title_flag']) ? $result['model_flag'] : false);
                    $amznProductInterface->
                    setBrandFlag(isset($result['brand_flag']) ? $result['brand_flag'] : false);
                    $amznProductInterface->
                    setManufacturerFlag(isset($result['manufacturer_flag']) ? $result['manufacturer_flag'] : false);
                    $amznProductInterface->
                    setModelFlag(isset($result['model_flag']) ? $result['model_flag'] : false);
                    $amznProductInterface->setAutoAssignedFlag('true');
                    $this->productResourceModel->save($amznProductInterface);
                }
            }
        }
    }

    /**
     * @param \Ced\Amazon\Model\Profile $profile
     * @param $productId
     * @param string $idType
     * @return array
     */
    public function load($profile, $productId, $idType = "ASIN")
    {
        $result = [
            'profile_id' => $profile->getId(),
            'profile_name' => $profile->getName(),
            'store_id' => $profile->getStoreId(),
            'account_id' => $profile->getAccountId(),
            'product' => []
        ];

        $config = $this->account->getById($profile->getAccountId())->getConfig($profile->getMarketplaceIds());
        $productList = $this->productList->create(
            [
                'config' => $config,
                'logger' => $this->logger
            ]
        );
        $productList->setIdType($idType);
        $productList->setProductIds($productId);
        $productList->fetchProductList();
        $products = $productList->getProduct();
        if ($products != false && !isset($products['Error'])) {
            /** @var \Amazon\Sdk\Api\Product $product */
            foreach ($products as $product) {
                // TODO: check if multiple
                $result['product'] = $product->getData();
            }
        }
        return $result;
    }

    /**
     * Match add save in Amazon Product Table
     * @param \Magento\Catalog\Model\Product $product
     * @param \Ced\Amazon\Api\Data\Search\ProductInterface $search
     * @return array
     */
    private function match($product, $search)
    {
        $result = [];

        foreach ($this->rules as $id => $rule) {
            if ($id == AttributeInterface::COLUMN_MODEL && empty($search->getData($id))) {
                $result[$id . "_flag"] = true;
                continue;
            }

            if ($rule['active'] &&
                $this->stringMatch($search->getData($id), $product->getData($this->strategyMapAttribute[$id]))
                >= $rule["percentage"]
            ) {
                $result[$id . "_flag"] = true;
            }
        }

        return $result;
    }

    /**
     * Match 2 Strings and returns matching percentage
     *
     * @param $searchString
     * @param $productString
     * @return string
     */
    public function stringMatch($searchString, $productString)
    {
        $count = 0;
        $searchArray = explode(" ", strtolower($searchString));
        $productArray = explode(" ", strtolower($productString));
        foreach ($productArray as $search) {
            if (in_array($search, $searchArray)) {
                $count++;
            }
        }
        $searchCount = count($searchArray);
        return number_format((float)($count * 100) / $searchCount, 4, '.', '');
    }

    public function getResult($json = true)
    {
        return $this->result;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     *
     */
    private function save($products)
    {
        $storeId = null;
        if ($this->storeManager->hasSingleStore()) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products as $product) {
            try {
                if (isset($storeId)) {
                    $product->setStoreId($storeId);
                }

                $this->productResource->saveAttribute(
                    $product,
                    \Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PRODUCT_STATUS
                );
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}
