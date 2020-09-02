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

namespace Ced\Amazon\Api;

interface StrategyRepositoryInterface
{
    /**
     * Get a Strategy by Id
     * @param string $id
     * @param boolean $loadTypeObject
     * @return \Ced\Amazon\Api\Data\StrategyInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id, $loadTypeObject = false);

    /**
     * Get a Strategy by Rule
     * @param \Magento\Catalog\Model\Product $product
     * @return null|\Ced\Amazon\Model\Strategy
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByRule($product);
}
