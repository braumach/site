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
 * Interface InventoryInterface
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface InventoryInterface
{
    const COLUMN_INVENTORY_STRATEGY_ID = 'inventory_strategy_id';
    const COLUMN_INVENTORY_RELATION_ID = 'strategy_id';

    const COLUMN_THRESHOLD = 'threshold';
    const COLUMN_THRESHOLD_BREAKPOINT = 'threshold_breakpoint';
    const COLUMN_THRESHOLD_GREATER_THAN_VALUE = 'threshold_greater';
    const COLUMN_THRESHOLD_LESS_THAN_VALUE = 'threshold_less';
    const COLUMN_FULFILLMENT_LATENCY = 'fulfillment_latency';
    const COLUMN_INVENTORY_OVERRIDE = 'override_inventory';

    const COLUMN_INVENTORY_TYPE = 'inventory_type';
    const INVENTORY_TYPE_LOOKUP = 'lookup';
    const INVENTORY_TYPE_AVAILABLE = 'available';
    const INVENTORY_TYPE_QUANTITY = 'quantity';
    const INVENTORY_TYPES = [
        self::INVENTORY_TYPE_LOOKUP,
        self::INVENTORY_TYPE_AVAILABLE,
        self::INVENTORY_TYPE_QUANTITY,
    ];

    const COLUMN_FULFILLMENT_CENTER_ID_ATTRIBUTE = 'fulfillment_center_id_attribute';
    const COLUMN_FULFILLMENT_CENTER_ID = 'fulfillment_center_id';

    const COLUMN_AVAILABLE_ATTRIBUTE = 'available_attribute';
    const COLUMN_AVAILABLE = 'available';

    const COLUMN_QUANTITY_ATTRIBUTE = 'quantity_attribute';
    const COLUMN_QUANTITY = 'quantity';
    const COLUMN_MARKETPLACE_QUANTITY_ENABLE = 'marketplace_quantity_enable';
    const COLUMN_MARKETPLACE_QUANTITY = 'marketplace_quantity';
    const DEFAULT_QUANTITY_ATTRIBUTE = 'quantity_and_stock_status';

    const COLUMN_RESTOCK_DATE_ATTRIBUTE = 'restock_date_attribute';
    const COLUMN_RESTOCK_DATE = 'restock_date';

    /**
     * Get Threshold
     * @return boolean
     */
    public function getThreshold();

    /**
     * Get Threshold Breakpoint
     * @return int
     */
    public function getThresholdBreakpoint();

    /**
     * Get Threshold Greater Than Value
     * @return int
     */
    public function getThresholdGreater();

    /**
     * Get Threshold Less Than Value
     * @return int
     */
    public function getThresholdLess();

    /**
     * Get Fulfillment Latency
     * @return int
     */
    public function getFulfillmentLatency();

    /**
     * Get Inventory Override
     * @return boolean
     */
    public function getInventoryOverride();

    /**
     * Get Inventory Type
     * @return string
     */
    public function getInventoryType();

    /**
     * Set Inventory Type
     * @param  string $type
     * @return string
     */
    public function setInventoryType($type);

    /**
     * Set Fulfillment Center Id Attribute
     * @param string $attribute
     * @return $this
     */
    public function setFulfillmentCenterIdAttribute($attribute);

    /**
     * Get Fulfillment Center Id Attribute
     * @return string
     */
    public function getFulfillmentCenterIdAttribute();

    /**
     * Get Fulfillment Center Id Value
     * @return string
     */
    public function getFulfillmentCenterId();

    /**
     * Set Fulfillment Center Id Value
     * @param string $id
     * @return $this
     */
    public function setFulfillmentCenterId($id);

    /**
     * Set Available Attribute
     * @param string $attribute
     * @return $this
     */
    public function setAvailableAttribute($attribute);

    /**
     * Get Available Attribute
     * @return string
     */
    public function getAvailableAttribute();

    /**
     * Get Available Value
     * @return string
     */
    public function getAvailable();

    /**
     * Set Available Value
     * @param string $value
     * @return $this
     */
    public function setAvailable($value);

    /**
     * Get Quantity Attribute
     * @return string
     */
    public function getQuantityAttribute();

    /**
     * Set Quantity Attribute
     * @param string $attribute
     * @return $this
     */
    public function setQuantityAttribute($attribute);

    /**
     * Set Marketplace Quantity Enable Flag
     * @param boolean $value
     * @return $this
     */
    public function setMarketplaceQuantityEnable($value);

    /**
     * Get Marketplace Quantity Enable Flag
     * @return boolean
     */
    public function getMarketplaceQuantityEnable();

    /**
     * Get Marketplace Quantity Mapping
     * @return string
     */
    public function getMarketplaceQuantity();

    /**
     * Set Marketplace Quantity Mapping
     * @param $value
     * @return $this
     */
    public function setMarketplaceQuantity($value);

    /**
     * Set RestockDate Attribute
     * @param $value
     * @return $this
     */
    public function setRestockDateAttribute($value);

    /**
     * Get RestockDate Attribute
     * @return string
     */
    public function getRestockDateAttribute();

    /**
     * Set RestockDate Value
     * @param $value
     * @return $this
     */
    public function setRestockDate($value);

    /**
     * Get RestockDate Value
     * @return string
     */
    public function getRestockDate();
}
