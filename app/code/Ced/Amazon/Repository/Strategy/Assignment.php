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
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Repository\Strategy;

use Ced\Amazon\Model\Cache;
use Ced\Amazon\Model\ResourceModel\Strategy;
use Ced\Amazon\Model\StrategyFactory;
use Ced\Amazon\Model\ResourceModel\Strategy\Assignment as ResourceModel;
use Ced\Amazon\Model\ResourceModel\Strategy as StrategyResourceModel;
use Ced\Amazon\Model\ResourceModel\Strategy\Assignment\CollectionFactory;
use Ced\Amazon\Model\Strategy\AssignmentFactory as ModelFactory;
use Ced\Amazon\Api\Strategy\AssignmentRepositoryInterface;

class Assignment implements AssignmentRepositoryInterface
{
    const CACHE_IDENTIFIER = "strategy_assignment_";

    /** @var Cache */
    private $cache;

    /**
     * @var ResourceModel
     */
    private $resource;

    /** @var StrategyResourceModel  */
    private $strategyResource;

    /**
     * @var ModelFactory
     */
    private $modelFactory;

    /** @var StrategyFactory  */
    private $strategyFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Cache $cache,
        ResourceModel $resource,
        StrategyResourceModel $strategyResourceModel,
        ModelFactory $modelFactory,
        StrategyFactory $strategyFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->cache = $cache;
        $this->resource = $resource;
        $this->strategyResource = $strategyResourceModel;
        $this->modelFactory = $modelFactory;
        $this->strategyFactory = $strategyFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get Strategy
     * @param $categoryId
     * @return \Ced\Amazon\Api\Data\Strategy\AssignmentInterface|null
     */
    public function getAssignmentStrategyByCategoryId($categoryId)
    {
        $strategy = null;

        if (!empty($categoryId)) {
            $strategy = $this->collectionFactory->create()->getAssignmentStrategyByCategoryId($categoryId);
        }

        return $strategy;
    }

    public function getOrCreateAssignmentStrategyByCategoryId($categoryId)
    {
        try {
            $strategy = $this->getAssignmentStrategyByCategoryId($categoryId);

            if (!isset($strategy) || empty($strategy->getCategoryId())) {
                /** @var \Ced\Amazon\Api\Data\StrategyInterface $strategy */
                $strategy = $this->strategyFactory->create();
                $strategy->setName("Assignment Strategy #" . $categoryId);
                $strategy->setType(\Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_ASSIGNMENT);
                $strategy->setActive(true);
                $this->strategyResource->save($strategy);
                $strategyId = $strategy->getId();

                /** @var \Ced\Amazon\Api\Data\Strategy\AssignmentInterface $assignment */
                $assignment = $this->modelFactory->create();
                $assignment->setCategoryId($categoryId);
                $assignment->setStrategyId($strategyId);
                $this->resource->save($assignment);
            }
        } catch (\Exception $e) {
            $strategy = null;
        }

        return $strategy;
    }

    public function getCategoryIdByStrategyId($strategyId)
    {
        $categoryId = null;

        if (!empty($strategyId)) {
            $categoryId = $this->collectionFactory->create()
                ->addFieldToFilter(
                    \Ced\Amazon\Api\Data\Strategy\AssignmentInterface::COLUMN_ASSIGNMENT_RELATION_ID,
                    [
                        'eq' => $strategyId
                    ]
                )
                ->getFirstItem()
                ->getCategoryId();
        }

        return $categoryId;
    }
}
