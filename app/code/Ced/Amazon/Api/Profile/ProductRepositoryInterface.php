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

namespace Ced\Amazon\Api\Profile;

/**
 * Interface ProductRepositoryInterface
 * @package Ced\Amazon\Api
 * @api
 */
interface ProductRepositoryInterface extends \Ced\Integrator\Api\Profile\ProductRepositoryInterface
{
    /**
     * Get Profile Ids By Product Ids
     * @param array $ids
     * @param bool $storeWise
     * @return mixed
     */
    public function getProfileIdsByProductIds(array $ids = [], $storeWise = false);

    /**
     * @param \Ced\Amazon\Api\Data\Profile\ProductInterface $product
     * @return int
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\Ced\Amazon\Api\Data\Profile\ProductInterface $product);

}
