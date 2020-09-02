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

use Ced\Amazon\Api\Data\Strategy\InventoryInterface;

class Inventory extends \Magento\Framework\Model\AbstractModel implements InventoryInterface
{
    const NAME = 'ced_amazon_strategy_inventory';

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Strategy\Inventory::class);
    }

    /**
     * Get Threshold
     * @return boolean
     */
    public function getThreshold()
    {
        return $this->getData(self::COLUMN_THRESHOLD);
    }

    /**
     * Get Threshold Breakpoint
     * @return int
     */
    public function getThresholdBreakpoint()
    {
        return $this->getData(self::COLUMN_THRESHOLD_BREAKPOINT);
    }

    /**
     * Get Threshold Greater Than Value
     * @return int
     */
    public function getThresholdGreater()
    {
        return $this->getData(self::COLUMN_THRESHOLD_GREATER_THAN_VALUE);
    }

    /**
     * Get Threshold Less Than Value
     * @return int
     */
    public function getThresholdLess()
    {
        return $this->getData(self::COLUMN_THRESHOLD_LESS_THAN_VALUE);
    }

    /**
     * Get Fulfillment Latency
     * @return int
     */
    public function getFulfillmentLatency()
    {
        return $this->getData(self::COLUMN_FULFILLMENT_LATENCY);
    }

    /**
     * Get Inventory Override
     * @return boolean
     */
    public function getInventoryOverride()
    {
        return $this->getData(self::COLUMN_INVENTORY_OVERRIDE);
    }

    /**
     * Get Inventory Type
     * @return string
     */
    public function getInventoryType()
    {
        return $this->getData(self::COLUMN_INVENTORY_TYPE);
    }

    /**
     * Set Inventory Type
     * @param string $type
     * @return string
     */
    public function setInventoryType($type)
    {
        return $this->setData(self::COLUMN_INVENTORY_TYPE, $type);
    }

    /**
     * Set Fulfillment Center Id Attribute
     * @param string $attribute
     * @return $this
     */
    public function setFulfillmentCenterIdAttribute($attribute)
    {
        return $this->setData(self::COLUMN_FULFILLMENT_CENTER_ID_ATTRIBUTE, $attribute);
    }

    /**
     * Get Fulfillment Center Id Attribute
     * @return string
     */
    public function getFulfillmentCenterIdAttribute()
    {
        return $this->getData(self::COLUMN_FULFILLMENT_CENTER_ID_ATTRIBUTE);
    }

    /**
     * Get Fulfillment Center Id Value
     * @return string
     */
    public function getFulfillmentCenterId()
    {
        return $this->getData(self::COLUMN_FULFILLMENT_CENTER_ID);
    }

    /**
     * Set Fulfillment Center Id Value
     * @param string $id
     * @return $this
     */
    public function setFulfillmentCenterId($id)
    {
        return $this->setData(self::COLUMN_FULFILLMENT_CENTER_ID, $id);
    }

    /**
     * Set Available Attribute
     * @param string $attribute
     * @return $this
     */
    public function setAvailableAttribute($attribute)
    {
        return $this->setData(self::COLUMN_AVAILABLE_ATTRIBUTE, $attribute);
    }

    /**
     * Get Available Attribute
     * @return string
     */
    public function getAvailableAttribute()
    {
        return $this->getData(self::COLUMN_AVAILABLE_ATTRIBUTE);
    }

    /**
     * Get Available Value
     * @return string
     */
    public function getAvailable()
    {
        return $this->getData(self::COLUMN_AVAILABLE);
    }

    /**
     * Set Available Value
     * @param string $value
     * @return $this
     */
    public function setAvailable($value)
    {
        return $this->setData(self::COLUMN_AVAILABLE, $value);
    }

    /**
     * Get Quantity Attribute
     * @return string
     */
    public function getQuantityAttribute()
    {
        return $this->getData(self::COLUMN_QUANTITY_ATTRIBUTE);
    }

    /**
     * Set Quantity Attribute
     * @param string $attribute
     * @return $this
     */
    public function setQuantityAttribute($attribute)
    {
        return $this->setData(self::COLUMN_QUANTITY_ATTRIBUTE, $attribute);
    }

    /**
     * Set Marketplace Quantity Enable Flag
     * @param boolean $value
     * @return $this
     */
    public function setMarketplaceQuantityEnable($value)
    {
        return $this->setData(self::COLUMN_MARKETPLACE_QUANTITY_ENABLE, $value);
    }

    /**
     * Get Marketplace Quantity Enable Flag
     * @return boolean
     */
    public function getMarketplaceQuantityEnable()
    {
        return $this->getData(self::COLUMN_MARKETPLACE_QUANTITY_ENABLE);
    }

    /**
     * Get Marketplace Quantity Mapping
     * @return string
     */
    public function getMarketplaceQuantity()
    {
        return $this->getData(self::COLUMN_MARKETPLACE_QUANTITY);
    }

    /**
     * Set Marketplace Quantity Mapping
     * @param $value
     * @return $this
     */
    public function setMarketplaceQuantity($value)
    {
        return $this->setData(self::COLUMN_MARKETPLACE_QUANTITY, $value);
    }

    /**
     * Set RestockDate Attribute
     * @param $value
     * @return $this
     */
    public function setRestockDateAttribute($value)
    {
        return $this->setData(self::COLUMN_RESTOCK_DATE_ATTRIBUTE, $value);
    }

    /**
     * Get RestockDate Attribute
     * @return string
     */
    public function getRestockDateAttribute()
    {
        return $this->getData(self::COLUMN_RESTOCK_DATE_ATTRIBUTE);
    }

    /**
     * Set RestockDate Value
     * @param $value
     * @return $this
     */
    public function setRestockDate($value)
    {
        return $this->setData(self::COLUMN_RESTOCK_DATE, $value);
    }

    /**
     * Get RestockDate Value
     * @return string
     */
    public function getRestockDate()
    {
        return $this->getData(self::COLUMN_RESTOCK_DATE);
    }
}
