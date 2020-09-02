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

//use Magento\Tests\NamingConvention\true\string;

/**
 * Interface ProductInterface
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface ProductInterface
{
    const COLUMN_ID = 'id';
    const COLUMN_RELATION_ID = 'relation_id';
    const COLUMN_ASIN = 'asin';
    const COLUMN_MARKETPLACE = 'marketplace';
    const COLUMN_CHANNEL = 'channel';
    const COLUMN_STATUS = 'status';
    const COLUMN_VALIDATION_ERRORS = 'validation_errors';
    const COLUMN_FEED_ERRORS = 'feed_errors';
    const COLUMN_UPDATE_LOG = 'update_log';
    const COLUMN_INVENTORY_LOG = 'inventory_log';
    const COLUMN_PRICE_LOG = 'price_log';
    const COLUMN_ACCOUNT_ID = 'account_id';
    const COLUMN_TITLE = 'title_flag';
    const COLUMN_BRAND = 'brand_flag';
    const COLUMN_MANUFACTURER = 'manufacturer_flag';
    const COLUMN_MARKETPLACE_ID = 'marketplace_id';
    const COLUMN_MODEL = 'model_flag';
    const COLUMN_AUTO_ASSIGNED = 'auto_assigned_flag';
    const COLUMN_MANUALLY_ASSIGNED = 'manually_assigned_flag';
    const VALUE_SEPARATOR = "0d64bf77f32eb8d87ab6ccaec74658b9";

    /**
     * @param $relationId
     * @return $this
     */
    public function setRelationId($relationId);

    /**
     * @return integer
     */
    public function getRelationId();

    /**
     * @param $asin
     * @return $this
     */
    public function setAsin($asin);

    /**
     * @return mixed
     */
    public function getAsin();

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @param $error
     * @return $this
     */
    public function setValidationErrors($error);

    /**
     * @return string
     */
    public function getValidationErrors();

    /**
     * @param $error
     * @return $this
     */
    public function setFeedErrors($error);

    /**
     * @return string
     */
    public function getFeedErrors();

    /**
     * @param $titleFlag
     * @return $this
     */
    public function setTitleFlag($titleFlag);

    /**
     * @return boolean
     */
    public function getTitleFlag();

    /**
     * @param $accountId
     * @return mixed
     */
    public function setAccountID($accountId);

    /**
     * @return string
     */
    public function getAccountID();

    /**
     * @param $brandFlag
     * @return $this
     */
    public function setBrandFlag($brandFlag);

    /**
     * @return boolean
     */
    public function getBrandFlag();

    /**
     * @param $manufacturerFlag
     * @return $this
     */
    public function setManufacturerFlag($manufacturerFlag);

    /**@param $modelFlag
     * @return $this
     */
    public function setModelFlag($modelFlag);

    /**
     * @return boolean
     */
    public function getManufacturerFlag();

    /**
     * @param $marketPlaceId
     * @return $this
     */
    public function setMarketplaceId($marketPlaceId);

    /**
     * @return string
     */
    public function getMarketplaceId();

    /**
     * Set Auto Assigned Flag
     * @param boolean $value
     * @return $this
     */
    public function setAutoAssignedFlag($value);

    /**
     * Set Manually Assigned Flag
     * @param boolean $value
     * @return $this
     */
    public function setManuallyAssignedFlag($value);

    /**
     * Set Update Log
     * @param $value
     * @return $this
     */
    public function setUpdateLog($value);

    /**
     * Set Inventory Update Log
     * @param $value
     * @return mixed
     */
    public function setInventoryLog($value);

    /**
     * Set Price Update Log
     * @param $value
     * @return mixed
     */
    public function setPriceLog($value);
}
