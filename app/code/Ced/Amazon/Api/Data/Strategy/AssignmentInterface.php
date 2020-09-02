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

namespace Ced\Amazon\Api\Data\Strategy;

/**
 * Interface AssignmentInterface, All Product assignment rules should be added in this strategy.
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface AssignmentInterface
{
    const COLUMN_ASSIGNMENT_STRATEGY_ID = 'assignment_strategy_id';
    const COLUMN_ASSIGNMENT_RELATION_ID = 'strategy_id';

    const COLUMN_CATEGORY_ID = 'category_id';

    /**
     * Get Category Id
     * @return string
     */
    public function getCategoryId();

    /**
     * Set Category Id
     * @param int $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId);

    /**
     * Set Strategy Id
     * @param int $strategyId
     * @return $this
     */
    public function setStrategyId($strategyId);

    /**
     * Get Strategy Id
     * @return int
     */
    public function getStrategyId();
}
