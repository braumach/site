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
 * Interface AttributeInterface
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface AttributeInterface
{
    const COLUMN_STRATEGY_ATTRIBUTE_ID = 'id';
    const COLUMN_ATTRIBUTE_RELATION_ID = 'strategy_id';

    const COLUMN_PRODUCT_TYPE = 'product_type';
    const COLUMN_PRODUCT_SUB_TYPE = 'product_sub_type';
    const COLUMN_BROWSE_NODES = 'browse_nodes';

    const COLUMN_SKU = 'sku';
    const COLUMN_ASIN = 'asin';
    const COLUMN_UPC = 'upc';
    const COLUMN_EAN = 'ean';
    const COLUMN_GTIN = 'gtin';

    const COLUMN_TITLE = 'title';
    const COLUMN_DESCRIPTION = 'description';
    const COLUMN_BRAND = 'brand';
    const COLUMN_MANUFACTURER = 'manufacturer';
    const COLUMN_MODEL = 'model';

    const ATTRIBUTES = [
        self::COLUMN_SKU => 'SKU',
        self::COLUMN_UPC => 'StandardProductID_Value',
        self::COLUMN_TITLE => 'DescriptionData_Title',
        self::COLUMN_BRAND => 'DescriptionData_Brand',
        self::COLUMN_DESCRIPTION => 'DescriptionData_Description',
    ];

    const COLUMN_ADDITIONAL_ATTRIBUTES = 'additional_attributes';
    const COLUMN_UNITS = 'units';

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getSku();

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getAsin();

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getUpc();

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getEan();

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getGtin();

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getTitle();

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getDesctiption();

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getBrand();

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getManufacturer();

    /**
     * @param $strategyId
     * @return mixed
     */
    public function getAttributeMapping();

    /**
     * Get Additional Attributes
     * @return string
     */
    public function getAdditionalAttributes();

    /**
     * Get Attributes as an array
     * @return mixed
     */
    public function getAttributes();

    /**
     * Get Model
     * @return string
     */
    public function getModel();
}
