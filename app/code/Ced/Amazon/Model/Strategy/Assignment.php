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

namespace Ced\Amazon\Model\Strategy;

use Ced\Amazon\Api\Data\Strategy\AssignmentInterface;

class Assignment extends \Magento\Framework\Model\AbstractModel implements AssignmentInterface
{
    const NAME = 'ced_amazon_strategy_assignment';

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Strategy\Assignment::class);
    }

    /**
     * Get Category Id
     * @return string
     */
    public function getCategoryId()
    {
        return $this->getData(static::COLUMN_CATEGORY_ID);
    }

    /**
     * Set Category Id
     * @param int $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(static::COLUMN_CATEGORY_ID, $categoryId);
    }

    /**
     * Set Strategy Id
     * @param int $strategyId
     * @return $this
     */
    public function setStrategyId($strategyId)
    {
        return $this->setData(static::COLUMN_ASSIGNMENT_RELATION_ID, $strategyId);
    }

    /**
     * Get Strategy Id
     * @return int
     */
    public function getStrategyId()
    {
        return $this->getData(static::COLUMN_ASSIGNMENT_RELATION_ID);
    }
}
