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

namespace Ced\Amazon\Model;

use Ced\Amazon\Api\Data\StrategyInterface;

class Strategy extends \Magento\Framework\Model\AbstractModel implements StrategyInterface
{
    const NAME = 'ced_amazon_strategy';

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Strategy::class);
    }

    /**
     * Set Name
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(static::COLUMN_NAME, $name);
    }

    /**
     * Set Type
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(static::COLUMN_TYPE, $type);
    }

    /**
     * Set Active
     * @param boolean $active
     * @return $this
     */
    public function setActive($active)
    {
        return $this->setData(static::COLUMN_ACTIVE, $active);
    }

    /**
     * Get Type
     * @return string
     */
    public function getType()
    {
        return $this->getData(static::COLUMN_TYPE);
    }
}
