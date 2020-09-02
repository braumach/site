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
 * Interface ProfileInterface
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface ProfileInterface extends \Ced\Integrator\Api\Data\ProfileInterface
{
    const COLUMN_ID = 'id';
    const COLUMN_NAME = 'profile_name';
    const COLUMN_CODE = 'profile_code';
    const COLUMN_STATUS = 'profile_status';

    const COLUMN_CATEGORY = 'profile_category';
    const COLUMN_SUB_CATEGORY = 'profile_sub_category';
    const COLUMN_ATTRIBUTES = 'profile_attributes'; // merged values, not saved.
    const COLUMN_REQUIRED_ATTRIBUTES = 'profile_required_attributes';
    const COLUMN_OPTIONAL_ATTRIBUTES = 'profile_optional_attributes';

    const COLUMN_MARKETPLACE = 'marketplace';
    const COLUMN_ACCOUNT_ID = 'account_id';
    const COLUMN_STORE_ID = 'store_id';
    const COLUMN_QUERY = 'query';
    const COLUMN_TYPE = 'type';

    const COLUMN_STRATEGY = 'strategy';
    const COLUMN_STRATEGY_ATTRIBUTE = 'strategy_attribute';
    const COLUMN_STRATEGY_INVENTORY = 'strategy_inventory';
    const COLUMN_STRATEGY_PRICE = 'strategy_price';
    const COLUMN_STRATEGY_SHIPPING = 'strategy_shipping';

    const COLUMN_AUTO_ASSIGNMENT = 'auto_assignment';
    const COLUMN_STRATEGY_ASSIGNMENT = 'strategy_assignment';

    const COLUMN_FILTER = 'filter'; // Removed. Do not use.

    const COLUMN_BARCODE_EXEMPTION = 'barcode_exemption';
    const COLUMN_MAGENTO_CATEGORY='magento_category';

    const COLUMN_REQUIRED = [
        self::COLUMN_NAME,
        self::COLUMN_MARKETPLACE,
        self::COLUMN_ACCOUNT_ID,
        self::COLUMN_CATEGORY,
        self::COLUMN_SUB_CATEGORY,
    ];

    /**
     * Get profile store id
     * @return int
     */
    public function getStoreId();

    /**
     * Get associated account with profile
     * @return integer|null
     */
    public function getAccountId();

    /**
     * Get profile's comma separated marketplace ids
     * @return string
     */
    public function getMarketplace();

    /**
     * Get profile's marketplace ids as an array
     * @return array
     */
    public function getMarketplaceIds();


    /**
     * @return mixed
     */
    public function getMagentoCategory();




    /**
     * Set Status
     * @param int $status
     * @return $this
     */
    public function setProfileSatus($status);

    /**
     * Get Profile Store
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore();

    /**
     * Get Profile Category
     * @return string
     */
    public function getProfileCategory();

    /**
     * Get Profile Sub Category
     * @return string
     */
    public function getProfileSubCategory();

    /**
     * Get Profile Attributes
     * @return array
     */
    public function getProfileAttributes();

    /**
     * Get Barcode Exemption
     * @return bool
     */
    public function getBarcodeExemption();

    /**
     * Set Barcode Exemption
     * @param boolean $value
     * @return $this
     */
    public function setBarcodeExemption($value);

    /**
     * Get Strategy Attributes
     * @return mixed
     */
    public function getStrategyAttribute();

    /**
     * Get Profile Type
     * @return string
     */
    public function getType();
}
