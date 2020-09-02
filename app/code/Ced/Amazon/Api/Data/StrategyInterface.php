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

namespace Ced\Amazon\Api\Data;

/**
 * Interface StrategyInterface
 * @package Ced\Amazon\Api\Data
 * @api
 * @method getId()
 */
interface StrategyInterface
{
    const COLUMN_ID = 'id';
    const COLUMN_ACTIVE = 'active';
    const COLUMN_TYPE = 'type';
    const COLUMN_NAME = 'name';

    /**
     * Set Name
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Set Type
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get Type
     * @return string
     */
    public function getType();

    /**
     * Set Active
     * @param boolean $active
     * @return $this
     */
    public function setActive($active);
}
