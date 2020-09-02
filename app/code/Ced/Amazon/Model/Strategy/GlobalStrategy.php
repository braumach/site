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

use Ced\Amazon\Api\Data\Strategy\GlobalStrategyInterface;
use Ced\Amazon\Api\Data\Strategy\InventoryInterface;
use Ced\Amazon\Api\Data\Strategy\ShippingInterface;

class GlobalStrategy extends \Magento\Framework\Model\AbstractModel implements
    GlobalStrategyInterface,
    InventoryInterface,
    ShippingInterface
{
    const NAME = 'ced_amazon_strategy_global';

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Strategy\GlobalStrategy::class);
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
     * Get Shipping Name
     * @return int
     */
    public function getShippingGroupName()
    {
        return $this->getData(self::COLUMN_MERCHANT_SHIPPING_GROUP_NAME);
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
        return InventoryInterface::INVENTORY_TYPE_QUANTITY;
    }

    /**
     * Set Inventory Type
     * @param string $type
     * @return string
     */
    public function setInventoryType($type)
    {
        return $this;
    }

    /**
     * Set Fulfillment Center Id Attribute
     * @param string $attribute
     * @return $this
     */
    public function setFulfillmentCenterIdAttribute($attribute)
    {
        return $this;
    }

    /**
     * Get Fulfillment Center Id Attribute
     * @return string
     */
    public function getFulfillmentCenterIdAttribute()
    {
        return '';
    }

    /**
     * Get Fulfillment Center Id Value
     * @return string
     */
    public function getFulfillmentCenterId()
    {
        return '';
    }

    /**
     * Set Fulfillment Center Id Value
     * @param string $id
     * @return $this
     */
    public function setFulfillmentCenterId($id)
    {
        return $this;
    }

    /**
     * Set Available Attribute
     * @param string $attribute
     * @return $this
     */
    public function setAvailableAttribute($attribute)
    {
        return $this;
    }

    /**
     * Get Available Attribute
     * @return string
     */
    public function getAvailableAttribute()
    {
        return '';
    }

    /**
     * Get Available Value
     * @return string
     */
    public function getAvailable()
    {
        return '';
    }

    /**
     * Set Available Value
     * @param string $value
     * @return $this
     */
    public function setAvailable($value)
    {
        return $this;
    }

    /**
     * Get Quantity Attribute
     * @return string
     */
    public function getQuantityAttribute()
    {
        return InventoryInterface::DEFAULT_QUANTITY_ATTRIBUTE;
    }

    /**
     * Set Quantity Attribute
     * @param string $attribute
     * @return $this
     */
    public function setQuantityAttribute($attribute)
    {
        return $this;
    }

    /**
     * Set Marketplace Quantity Enable Flag
     * @param boolean $value
     * @return $this
     */
    public function setMarketplaceQuantityEnable($value)
    {
        return $this;
    }

    /**
     * Get Marketplace Quantity Enable Flag
     * @return boolean
     */
    public function getMarketplaceQuantityEnable()
    {
        return '';
    }

    /**
     * Get Marketplace Quantity Mapping
     * @return string
     */
    public function getMarketplaceQuantity()
    {
        return '[]';
    }

    /**
     * Set Marketplace Quantity Mapping
     * @param $value
     * @return $this
     */
    public function setMarketplaceQuantity($value)
    {
        return $this;
    }

    /**
     * Set RestockDate Attribute
     * @param $value
     * @return $this
     */
    public function setRestockDateAttribute($value)
    {
        return $this;
    }

    /**
     * Get RestockDate Attribute
     * @return string
     */
    public function getRestockDateAttribute()
    {
        return '';
    }

    /**
     * Set RestockDate Value
     * @param $value
     * @return $this
     */
    public function setRestockDate($value)
    {
        return $this;
    }

    /**
     * Get RestockDate Value
     * @return string
     */
    public function getRestockDate()
    {
        return '';
    }
}
