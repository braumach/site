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

use Ced\Amazon\Api\Data\ProductInterface;

class Product extends \Magento\Framework\Model\AbstractModel implements ProductInterface
{
    const NAME = 'ced_amazon_product';

    public function setRelationId($relationId)
    {
        return $this->setData(self::COLUMN_RELATION_ID, $relationId);
    }

    public function getRelationId()
    {
        return $this->getData(self::COLUMN_RELATION_ID);
    }

    public function setAsin($asin)
    {
        return $this->setData(self::COLUMN_ASIN, $asin);
    }

    public function getAsin()
    {
        return $this->getData(self::COLUMN_ASIN);
    }

    public function setStatus($status)
    {
        return $this->setData(self::COLUMN_STATUS, $status);
    }

    public function getStatus()
    {
        return $this->getData(self::COLUMN_STATUS);
    }

    public function setValidationErrors($error)
    {
        return $this->setData(self::COLUMN_VALIDATION_ERRORS, $error);
    }

    public function getValidationErrors()
    {
        return $this->getData(self::COLUMN_VALIDATION_ERRORS);
    }
    public function setFeedErrors($error)
    {
        return $this->setData(self::COLUMN_FEED_ERRORS, $error);
    }

    public function getFeedErrors()
    {
        return $this->getData(self::COLUMN_FEED_ERRORS);
    }

    public function setAccountID($accountId)
    {
        return $this->setData(self::COLUMN_ACCOUNT_ID, $accountId);
    }

    public function getAccountID()
    {
        return $this->getData(self::COLUMN_ACCOUNT_ID);
    }

    public function setTitleFlag($titleFlag)
    {
        return $this->setData(self::COLUMN_TITLE, $titleFlag);
    }

    public function getTitleFlag()
    {
        return $this->getData(self::COLUMN_TITLE);
    }

    public function setBrandFlag($brandFlag)
    {
        return $this->setData(self::COLUMN_BRAND, $brandFlag);
    }

    public function getBrandFlag()
    {
        return $this->getData(self::COLUMN_BRAND);
    }

    public function setManufacturerFlag($manufacturerFlag)
    {
        return $this->setData(self::COLUMN_MANUFACTURER, $manufacturerFlag);
    }

    public function getManufacturerFlag()
    {
        return $this->getData(self::COLUMN_MANUFACTURER);
    }

    public function setModelFlag($modelFlag)
    {
        return $this->setData(self::COLUMN_MODEL, $modelFlag);
    }

    public function getModelFlag()
    {
        return $this->getData(self::COLUMN_MODEL);
    }

    public function setMarketplaceId($marketPlaceId)
    {
        return $this->setData(self::COLUMN_MARKETPLACE_ID, $marketPlaceId);
    }

    public function getMarketplaceId()
    {
        return $this->getData(self::COLUMN_MARKETPLACE_ID);
    }

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Product::class);
    }

    /**
     * @inheritDoc
     */
    public function setAutoAssignedFlag($value)
    {
        return $this->setData(self::COLUMN_AUTO_ASSIGNED, $value);
    }

    /**
     * @inheritDoc
     */
    public function setManuallyAssignedFlag($value)
    {
        return $this->setData(self::COLUMN_MANUALLY_ASSIGNED, $value);
    }

    /**
     * Set Update Log
     * @param $value
     * @return $this
     */
    public function setUpdateLog($value)
    {
        return $this->setData(self::COLUMN_UPDATE_LOG, $value);
    }

    /**
     * Set Inventory Update Log
     * @param $value
     * @return mixed
     */
    public function setInventoryLog($value)
    {
        return $this->setData(self::COLUMN_INVENTORY_LOG, $value);
    }

    /**
     * Set Price Update Log
     * @param $value
     * @return mixed
     */
    public function setPriceLog($value)
    {
        return $this->setData(self::COLUMN_PRICE_LOG, $value);
    }
}
