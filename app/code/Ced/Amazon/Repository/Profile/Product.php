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

namespace Ced\Amazon\Repository\Profile;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;

class Product implements \Ced\Amazon\Api\Profile\ProductRepositoryInterface
{
    const CACHE_IDENTIFIER = "profile_product_table_";

    /** @var \Ced\Amazon\Model\Cache */
    private $cache;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Profile\Product
     */
    private $resource;

    /**
     * @var \Ced\Amazon\Model\Profile\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Profile\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Ced\Amazon\Api\Data\Profile\ProductSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface */
    private $pool = null;

    public function __construct(
        \Ced\Amazon\Model\Cache $cache,
        \Ced\Amazon\Model\ResourceModel\Profile\Product $resource,
        \Ced\Amazon\Model\ResourceModel\Profile\Product\CollectionFactory $collectionFactory,
        \Ced\Amazon\Api\Data\Profile\ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Ced\Amazon\Model\Profile\ProductFactory $productFactory
    ) {
        $this->cache = $cache;
        $this->resource = $resource;
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Delete all relations with provided profile_id
     * @param $profileId
     * @return bool|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByProfileId($profileId)
    {
        $status = $this->resource->deleteByProfileId($profileId);
        if (isset($this->pool[$profileId])) {
            unset($this->pool[$profileId]);
        }
        return $status;
    }

    /**
     * Get Ids by Profile Id
     * @param $profileId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductIdsByProfileId($profileId)
    {
        if (!isset($this->pool[$profileId]) || !is_array($this->pool[$profileId])) {
            $this->pool[$profileId] = $this->resource->getProductIdsByProfileId($profileId);
        }
        return $this->pool[$profileId];
    }

    /**
     * Get Profile Ids By Product Ids
     * @param array $ids
     * @param bool $storeWise
     * @return mixed
     */
    public function getProfileIdsByProductIds(array $ids = [], $storeWise = false)
    {
        $profileIds = [];
        try {
            $result = $this->resource->getProfileIdsByProductIds($ids);
            if ($storeWise) {
                $profileIds = $result;
            } else {
                foreach ($result as $storeId => $tmp) {
                    $profileIds = array_merge($profileIds, $tmp);
                }
                $profileIds = array_unique($profileIds);
            }
        } catch (\Exception $e) {
            // Silence
        }

        return $profileIds;
    }

    /**
     * Delete Product Ids from a Profile Id
     * @param array $productIds
     * @param int $profileId
     * @return bool|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByProductIdsAndProfileId(array $productIds, $profileId)
    {
        $status = $this->resource->deleteByProductIdsAndProfileId($productIds, $profileId);
        if (isset($this->pool[$profileId]) && $status) {
            unset($this->pool[$profileId]);
        }
        $this->cache->removeValue(self::CACHE_IDENTIFIER . $profileId);

        return $status;
    }

    /**
     * Get Loaded Products for a Profile
     * @param $profileId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductsByProfileId($profileId)
    {
        return $this->collectionFactory->create()->getProductsByProfileId($profileId);
    }

    /**
     * Add Relations
     * @param array $productIds
     * @param $profileId
     * @return bool|int|mixed
     */
    public function addProductsIdsWithProfileId(array $productIds, $profileId)
    {
        $status = $this->resource->addProductsIdsWithProfileId($productIds, $profileId);
        if ($status && isset($this->pool[$profileId])) {
            unset($this->pool[$profileId]);
        }
        $this->cache->removeValue(self::CACHE_IDENTIFIER . $profileId);

        return $status;
    }

    /**
     * Get all Products
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ced\Amazon\Api\Data\Profile\ProductSearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        /** @var \Magento\Framework\Api\Search\FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }

        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            if (isset($field)) {
                $collection->addOrder(
                    $field,
                    $this->getDirection($sortOrder->getDirection())
                );
            }
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        // Not loading all data, mappings need to be decoded. get ids and taking data from cache
        $collection->addFieldToSelect('id');
        $collection->load();
        $products = [];
        /** @var \Ced\Amazon\Model\Profile\Product $product */
        foreach ($collection as $product) {
            $products[$product->getId()] = $product;
        }

        /** @var \Ced\Amazon\Api\Data\Profile\ProductSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
//        $searchResults->setCriteria($searchCriteria);
        $searchResults->setItems($products);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param \Magento\Framework\Api\Search\FilterGroup $gr $profileIdoup
     * @param \Ced\Amazon\Model\ResourceModel\Profile\Product\Collection $collection
     */
    private function addFilterGroupToCollection($group, $collection)
    {
        $fields = [];
        $conditions = [];

        foreach ($group->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $field = $filter->getField();
            $value = $filter->getValue();
            $fields[] = $field;
            $conditions[] = [$condition => $value];
        }

        $collection->addFieldToFilter($fields, $conditions);
    }

    private function getDirection($direction)
    {
        return $direction == SortOrder::SORT_ASC ?: SortOrder::SORT_DESC;
    }

    /**
     * @param \Ced\Amazon\Api\Data\Profile\ProductInterface $product
     * @return int
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\Ced\Amazon\Api\Data\Profile\ProductInterface $product)
    {
        if ($product->getId() > 0) {
            $this->clean($product->getId());
        }

        $this->resource->save($product);
        return $product->getId();
    }

    /**
     * Clear cache for a relation id
     * @param $id
     */
    public function clean($id)
    {
        if (!empty($this->pool)) {
            foreach ($this->pool as $profileId => $pool) {
                $relation = $this->getById($id);
                $productId = $relation->getData(\Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID);
                $profileId = $relation->getData(\Ced\Amazon\Model\Profile\Product::COLUMN_PROFILE_ID);
                if (empty($productId) && in_array($productId, $pool)) {
                    unset($this->pool[$profileId]);
                    $this->cache->removeValue(self::CACHE_IDENTIFIER . $profileId);
                }
            }
        }
    }

    /**
     * Get a Relation by Id
     * @param string $id
     * @return \Ced\Amazon\Api\Data\Profile\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $product = $this->productFactory->create();
        $this->resource->load($product, $id);

        if (!$product->getId()) {
            throw new NoSuchEntityException(__('Product does not exist.'));
        }

        return $product;
    }

    /**
     * @param \Ced\Amazon\Api\Data\Profile\ProductInterface $product
     * @param int $id
     * @return \Ced\Amazon\Api\Data\Profile\ProductInterface $product
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function load(\Ced\Amazon\Api\Data\Profile\ProductInterface $product, $id)
    {
        $this->resource->load($product, $id);
        return $product;
    }

    /**
     * Delete a relation by Id
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        $product = $this->productFactory->create();
        $product->setId($id);
        if ($this->resource->delete($product)) {
            $relation = $this->getById($id);
            $productId = $relation->getData(\Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID);
            foreach ($this->pool as $profileId => $pool) {
                if (in_array($productId, $pool)) {
                    unset($this->pool[$profileId]);
                    $this->cache->removeValue(self::CACHE_IDENTIFIER . $profileId);
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Clear cache for a profile id
     * @param int $profileId
     */
    public function cleanByProfileId($id)
    {
        if (!empty($this->pool)) {
            foreach ($this->pool as $profileId => $pool) {
                if ($id == $profileId) {
                    unset($this->pool[$profileId]);
                }
            }
            $this->cache->removeValue(self::CACHE_IDENTIFIER . $profileId);
        }
    }

    /**
     * TODO: dev
     * Refresh profile in cache
     * @param $id
     * @param \Ced\Amazon\Api\Data\Profile\ProductInterface
     * @throws \Exception
     */
    public function refresh($id, $product = null)
    {
        if (!isset($product)) {
            $product = $this->productFactory->create();
        }

        $this->resource->load($product, $id);
        $this->cache->setValue(self::CACHE_IDENTIFIER . $id, $product->getData());
    }

    /**
     * Check if Marketplace Product
     * @param $productId
     * @param $storeId
     * @return bool
     */
    public function isMarketplaceProduct($productId)
    {
        try {
            return $this->resource->checkIfExists($productId);
        } catch (\Exception $e) {
            return false;
        }
    }
}
