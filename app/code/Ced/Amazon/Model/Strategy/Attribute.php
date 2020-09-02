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

use Ced\Amazon\Api\Data\Strategy\AttributeInterface;

class Attribute extends \Magento\Framework\Model\AbstractModel implements AttributeInterface
{
    const NAME = 'ced_amazon_strategy_attribute';

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Strategy\Attribute::class);
    }

    /**
     * Get Additional Attributes
     * @return string
     */
    public function getAdditionalAttributes()
    {
        return $this->getData(static::COLUMN_ADDITIONAL_ATTRIBUTES);
    }

    /**
     * Get Attributes as an array
     * @return mixed
     */
    public function getAttributes()
    {
        $attributes = [];
        $value = $this->getAdditionalAttributes();
        if (!empty($value)) {
            $value = json_decode($value, true);
            if (is_array($value)) {
                $attributes = $value;
            }
        }

        return $attributes;
    }

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getSku()
    {
        return $this->getData(static::COLUMN_SKU);
    }

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getAsin()
    {
        return $this->getData(static::COLUMN_ASIN);
    }

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getUpc()
    {
        return $this->getData(static::COLUMN_UPC);
    }

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getEan()
    {
        return $this->getData(static::COLUMN_EAN);
    }

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getGtin()
    {
        return $this->getData(static::COLUMN_GTIN);
    }

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getData(static::COLUMN_TITLE);
    }

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getDesctiption()
    {
        return $this->getData(static::COLUMN_DESCRIPTION);
    }

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getBrand()
    {
        return $this->getData(static::COLUMN_BRAND);
    }

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getManufacturer()
    {
        return $this->getData(static::COLUMN_MANUFACTURER);
    }

    /**
     * @return array|mixed
     */
    public function getAttributeMapping()
    {
        return [
            "sku" => $this->getSku(),
            "asin" => $this->getAsin(),
            "upc" => $this->getUpc(),
            "ean" => $this->getEan(),
            "gtin" => $this->getGtin(),
            "title" => $this->getTitle(),
            "description" => $this->getDesctiption(),
            "brand" => $this->getBrand(),
            "manufacturer" => $this->getManufacturer(),
            "model" => $this->getModel(),
        ];
    }

    /**
     * Get Model
     * @return string
     */
    public function getModel()
    {
//        $model = null;
//        $additional = $this->getAttributes();
//        // TODO: add array search to get Model or MPN
//        if (isset($additional['DescriptionData_MfrPartNumber'])) {
//            $model = $additional['DescriptionData_MfrPartNumber'];
//        }
//        return $model;
        return $this->getData(static::COLUMN_MODEL);
    }
}
