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

namespace Ced\Amazon\Repository;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;

class Profile implements \Ced\Amazon\Api\ProfileRepositoryInterface
{
    const CACHE_IDENTIFIER = "profile_";

    /** @var \Ced\Amazon\Model\Cache  */
    private $cache;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Profile
     */
    private $resource;

    /**
     * @var \Ced\Amazon\Model\ProfileFactory
     */
    private $profileFactory;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /** @var Profile\Product  */
    private $productRepository;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  */
    private $catalog;

    /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface  */
    private $pool = null;

    /** @var array $relations, product to profile ids relations */
    private $relations = [];

    public function __construct(
        \Ced\Amazon\Model\Cache $cache,
        \Ced\Amazon\Model\ResourceModel\Profile $resource,
        \Ced\Amazon\Model\ProfileFactory $profileFactory,
        \Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory $collectionFactory,
        \Ced\Amazon\Api\Data\ProfileSearchResultsInterfaceFactory $searchResultsFactory,
        \Ced\Amazon\Repository\Profile\Product $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollectionFactory
    ) {
        $this->cache = $cache;
        $this->resource = $resource;
        $this->profileFactory = $profileFactory;
        $this->productRepository = $productRepository;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->catalog = $catalogCollectionFactory;
    }

    /**
     * Get object pool
     * @return \Ced\Amazon\Api\Data\ProfileSearchResultsInterface
     */
    private function getPool()
    {
        if (!isset($this->pool)) {
            $this->pool = $this->searchResultsFactory->create();
        }

        return $this->pool;
    }

    /**
     * Get a Profile by Id
     * @param string $id
     * @return \Ced\Amazon\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $profile = $this->getPool()->getItem($id);
        if (!isset($profile)) {
            $profile = $this->profileFactory->create();
            $data = $this->cache->getValue(self::CACHE_IDENTIFIER . $id);
            if (!empty($data)) {
                $profile->addData($data);
            } else {
                $this->refresh($id, $profile);
            }
        }

        if (!$profile->getId()) {
            throw new NoSuchEntityException(__('Profile does not exist.'));
        }

        return $profile;
    }

    /**
     * Get a Profiles by Product Id
     * @param string $productId
     * @return \Ced\Amazon\Api\Data\ProfileSearchResultsInterface|\Ced\Integrator\Api\Data\ProfileSearchResultsInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByProductId($productId)
    {
        /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        if (isset($this->relations[$productId])) {
            $profileIds = $this->relations[$productId];
        } else {
            $profileIds = $this->productRepository->getProfileIdsByProductIds([$productId]);
            $this->relations[$productId] = $profileIds;
        }

        foreach ($profileIds as $profileId) {
            $searchResults->setItem($profileId, $this->getById($profileId));
        }

        return $searchResults;
    }

    /**
     * @param array $productIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProfileIdsByProductIds(array $productIds = [])
    {
        $profileIds = $this->productRepository->getProfileIdsByProductIds($productIds);
        return $profileIds;
    }

    /**
     * @param $ids
     * @return \Ced\Amazon\Api\Data\ProfileSearchResultsInterface|mixed
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProfilesByProductIds($ids)
    {
        $profileIds = $this->productRepository->getProfileIdsByProductIds($ids);
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('id', ['in' => $profileIds]);
        /** @var \Ced\Amazon\Model\Profile $profile */
        foreach ($collection as $profile) {
            $profiles[$profile->getId()] = $this->getById($profile->getId());
        }

        /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($profiles);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Get all Profiles
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ced\Amazon\Api\Data\ProfileSearchResultsInterface|\Ced\Integrator\Api\Data\SearchResultsInterface
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
        $profiles = [];
        /** @var \Ced\Amazon\Model\Profile $profile */
        foreach ($collection as $profile) {
            $profiles[$profile->getId()] = $this->getById($profile->getId());
        }

        /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($profiles);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param \Ced\Amazon\Api\Data\ProfileInterface $profile
     * @return int
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\Ced\Amazon\Api\Data\ProfileInterface $profile)
    {
        if ($profile->getId() > 0) {
            $this->clean($profile->getId());
        }

        $this->resource->save($profile);
        return $profile->getId();
    }

    /**
     * @param \Ced\Amazon\Api\Data\ProfileInterface $profile
     * @param int $id
     * @return \Ced\Amazon\Api\Data\ProfileInterface $profile
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function load(\Ced\Amazon\Api\Data\ProfileInterface $profile, $id)
    {
        $this->resource->load($profile, $id);
        return $profile;
    }

    /**
     * Delete a profile
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        $profile = $this->profileFactory->create();
        $profile->setId($id);
        if ($this->resource->delete($profile)) {
            if (!empty($this->getPool()->getItem($id))) {
                $this->getPool()->setItem($id, null);
            }
            $this->cache->removeValue(self::CACHE_IDENTIFIER . $id);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Clear cache for a Profile id
     * @param $id
     */
    public function clean($id)
    {
        if (!empty($this->getPool()->getItem($id))) {
            $this->getPool()->setItem($id, null);
        }

        $this->cache->removeValue(self::CACHE_IDENTIFIER . $id);
    }

    /**
     * Refresh profile in cache
     * @param $id
     * @param \Ced\Amazon\Api\Data\ProfileInterface
     * @throws \Exception
     */
    public function refresh($id, $profile = null)
    {
        if (!isset($profile)) {
            $profile = $this->profileFactory->create();
        }

        $this->resource->load($profile, $id);
        $this->cache->setValue(self::CACHE_IDENTIFIER . $id, $profile->getData());
        $this->getPool()->setItem($id, $profile);
    }

    /**
     * @param \Magento\Framework\Api\Search\FilterGroup $group
     * @param \Ced\Amazon\Model\ResourceModel\Profile\Collection $collection
     */
    private function addFilterGroupToCollection($group, $collection)
    {
        $fields = [];
        $conditions = [];

        if (is_array($group) && isset($group['filters'])) {
            foreach ($group['filters'] as $filter) {
                if (isset($filter['field'], $filter['condition_type'], $filter['value'])) {
                    $fields[] = $filter['field'];
                    $conditions[] = [
                        $filter['condition_type'] => $filter['value']
                    ];
                }
            }
        } else {
            foreach ($group->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $field = $filter->getField();
                $value = $filter->getValue();
                $fields[] = $field;
                $conditions[] = [$condition=>$value];
            }
        }

        $collection->addFieldToFilter($fields, $conditions);
    }

    private function getDirection($direction)
    {
        return $direction == SortOrder::SORT_ASC ?: SortOrder::SORT_DESC;
    }

    /**
     * Get profile product ids
     * @param int $id
     * @param array $productIds
     * @param int $storeId
     * @return array
     * @throws  \Exception
     */
    public function getAssociatedProductIds($id, $storeId = 0, array $productIds = [])
    {
        $allIds = $this->productRepository->getProductIdsByProfileId($id);
        if (!empty($productIds)) {
            $allIds = array_intersect($allIds, $productIds);
        }

        return $allIds;
    }

    /**
     * Get profile products
     * @param int $id
     * @param int $storeId
     * @param array $productIds
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws  \Exception
     */
    public function getAssociatedProducts($id, $storeId = 0, array $productIds = [])
    {
        $allIds = $this->getAssociatedProductIds($id, $storeId, $productIds);
        $profile = $this->getById($id);
        $storeId = $profile->getStore()->getId();
        $products = $this->catalog->create()
            ->setStoreId($storeId)
            ->addAttributeToFilter('entity_id', ['in' => $allIds]);

        return $products;
    }

    /**
     * Get Profiles By Strategy Id
     * @param $strategyId
     * @return mixed
     */
    public function getProfileIdsByStrategyId($strategyId)
    {
        $profileIds = [];

        if (!empty($strategyId)) {
            $profileIds = $this->collectionFactory->create()
                ->addFieldToFilter(
                    \Ced\Amazon\Api\Data\ProfileInterface::COLUMN_STRATEGY_ASSIGNMENT,
                    ['eq', $strategyId]
                )
                ->getAllIds();
        }
        return $profileIds;
    }
}
