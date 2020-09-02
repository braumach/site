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

use Magento\Framework\Exception\NoSuchEntityException;
use Ced\Amazon\Model\ResourceModel\Strategy as StrategyResource;
use Ced\Amazon\Model\StrategyFactory;
use Ced\Amazon\Model\ResourceModel\Strategy\CollectionFactory;
use Ced\Amazon\Api\Data\StrategySearchResultsInterfaceFactory;
use Ced\Amazon\Model\Cache;

class Strategy implements \Ced\Amazon\Api\StrategyRepositoryInterface
{
    const CACHE_IDENTIFIER = "strategy_";

    /** @var \Ced\Amazon\Model\Cache */
    private $cache;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Strategy
     */
    private $resource;

    /**
     * @var \Ced\Amazon\Model\StrategyFactory
     */
    private $strategyFactory;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Strategy\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StrategySearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /** @var \Ced\Amazon\Api\Data\StrategySearchResultsInterface */
    private $pool = null;

    private $vendorIdRelation = [];

    private $strategyList = [];

    /**
     * Strategy constructor.
     * @param Cache $cache
     * @param StrategyResource $resource
     * @param StrategyFactory $strategyFactory
     * @param CollectionFactory $collectionFactory
     * @param StrategySearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        Cache $cache,
        StrategyResource $resource,
        StrategyFactory $strategyFactory,
        CollectionFactory $collectionFactory,
        StrategySearchResultsInterfaceFactory $searchResultsFactory,
        array $strategyList = []
    ) {
        $this->cache = $cache;
        $this->resource = $resource;
        $this->strategyFactory = $strategyFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->strategyList = $strategyList;
    }

    /**
     * Get object pool
     * @return \Ced\Amazon\Api\Data\StrategySearchResultsInterface
     */
    private function getPool()
    {
        if (!isset($this->pool)) {
            $this->pool = $this->searchResultsFactory->create();
        }

        return $this->pool;
    }

    /**
     * Get a Strategy by Id
     * @param string $id
     * @param boolean $loadTypeObject
     * @return \Ced\Amazon\Api\Data\StrategyInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id, $loadTypeObject = false)
    {
        $strategy = $this->getPool()->getItem($id);
        if (!isset($strategy)) {
            $strategy = $this->strategyFactory->create();
            $data = $this->cache->getValue(self::CACHE_IDENTIFIER . $id);
            if (!empty($data)) {
                $strategy->addData($data);
            } else {
                $this->refresh($id, $strategy);
            }

            if ($loadTypeObject) {
                $this->loadTypeObject($strategy);
            }
        }

        if (!$strategy->getId()) {
            throw new NoSuchEntityException(__('Strategy does not exist.'));
        }

        return $strategy;
    }

    /**
     * Get a Strategy by Rule
     * @param \Magento\Catalog\Model\Product $product
     * @return null|\Ced\Amazon\Model\Strategy
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByRule($product)
    {
        $vendor = null;
        $sku = $product->getSku();
        $skuParts = explode('-', $sku);
        if (is_array($skuParts) && count($skuParts) > 1 && $skuParts[0]) {
            $vendor = $skuParts[0];
        } else {
            $matches = [];
            preg_match('/^([A-Z]+)([0-9]+)$/i', $sku, $matches);
            if (isset($matches[1])) {
                $vendor = $matches[1];
            }
        }

        if (isset($this->vendorIdRelation[$vendor])) {
            /** @var \Ced\Amazon\Model\Strategy $strategy */
            $strategy = $this->getById($this->vendorIdRelation[$vendor]);
        } else {
            /** @var \Ced\Amazon\Model\Strategy $strategy */
            $strategy = $this->strategyFactory->create();
            $this->resource->load($strategy, $vendor, \Ced\Amazon\Model\Strategy::COLUMN_NAME);
            $this->vendorIdRelation[$vendor] = $strategy->getId();
        }

        if (empty($strategy->getId())) {
            throw new NoSuchEntityException(__("Strategy not available by auto assignment rule."));
        }

        return $strategy;
    }

    /**
     * @param \Ced\Amazon\Api\Data\StrategyInterface $strategy
     * @return int
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\Ced\Amazon\Api\Data\StrategyInterface $strategy)
    {
        if ($strategy->getId() > 0) {
            $this->clean($strategy->getId());
        }

        $this->resource->save($strategy);
        return $strategy->getId();
    }

    /**
     * @param \Ced\Amazon\Api\Data\StrategyInterface $strategy
     * @param int $id
     * @return \Ced\Amazon\Api\Data\StrategyInterface $strategy
     */
    public function load(\Ced\Amazon\Api\Data\StrategyInterface $strategy, $id)
    {
        $this->resource->load($strategy, $id);
        return $strategy;
    }

    /**
     * Delete a strategy
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        $strategy = $this->strategyFactory->create();
        $strategy->setId($id);
        if ($this->resource->delete($strategy)) {
            if (isset($this->pool[$id])) {
                unset($this->pool[$id]);
            }

            $this->cache->removeValue(self::CACHE_IDENTIFIER . $id);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Clear cache for a Strategy id
     * @param $id
     */
    public function clean($id)
    {
        if (isset($this->pool[$id])) {
            unset($this->pool[$id]);
        }

        $this->cache->removeValue(self::CACHE_IDENTIFIER . $id);
    }

    /**
     * Refresh strategy in cache
     * @param $id
     * @param \Ced\Amazon\Api\Data\StrategyInterface
     * @throws \Exception
     * \     */
    public function refresh($id, $strategy = null)
    {
        if (!isset($strategy)) {
            $strategy = $this->strategyFactory->create();
        }

        $this->resource->load($strategy, $id);

        $this->cache->setValue(self::CACHE_IDENTIFIER . $id, $strategy->getData());
    }

    private function loadTypeObject($strategy)
    {
        if ($strategy->getId() > 0 && isset($this->strategyList[$strategy->getType()])) {
            /** @var \Ced\Amazon\Api\Data\Strategy\AttributeInterface $model */
            $model = $this->strategyList[$strategy->getType()]['model_factory']->create();
            $resource = $this->strategyList[$strategy->getType()]['resource_model_factory']
                ->load(
                    $model,
                    $strategy->getId(),
                    \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_ATTRIBUTE_RELATION_ID
                );
            $strategy->setTypeObject($model);
        }
    }
}
