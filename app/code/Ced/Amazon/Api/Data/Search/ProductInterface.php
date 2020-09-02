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

namespace Ced\Amazon\Api\Data\Search;

/**
 * Interface ProductInterface
 * @package Ced\Amazon\Api\Data
 * @api
 * @method getData($key = '', $index = null)
 */
interface ProductInterface
{
    const COLUMN_ID = 'id';
    const COLUMN_IMAGE='image';
    const COLUMN_ASIN = 'asin';
    const COLUMN_UPC = 'upc';
    const COLUMN_GTIN = 'gtin';
    const COLUMN_EAN = 'ean';
    const COLUMN_BRAND = 'brand';
    const COLUMN_MODEL = 'model';
    const COLUMN_TITLE = 'title';
    const COLUMN_MANUFACTURER = 'manufacturer';
    const COLUMN_DESCRIPTION = 'description';
    const COLUMN_RESPONSE = 'response';
    const COLUMN_MARKETPLACE_ID = 'marketplace_id';

    const COLUMN_RELATION_ID = 'relation_id';

    /**
     * @param $asin
     * @return mixed
     */
    public function setAsin($asin);

    /**
     * @return mixed
     */
    public function getAsin();

    /**
     * @param $upc
     * @return mixed
     */
    public function setUpc($upc);

    /**
     * @return mixed
     */
    public function getUpc();

    /**
     * @param $gtin
     * @return mixed
     */
    public function setGtin($gtin);

    /**
     * @return mixed
     */
    public function getGtin();

    /**
     * @param $ean
     * @return mixed
     */
    public function setEan($ean);

    /**
     * @return mixed
     */
    public function getEan();

    /**
     * @param $brand
     * @return mixed
     */
    public function setBrand($brand);

    /**
     * @return mixed
     */
    public function getBrand();

    /**
     * @param $model
     * @return mixed
     */
    public function setModel($model);

    /**
     * @return mixed
     */
    public function getModel();

    /**
     * @param $title
     * @return mixed
     */
    public function setTitle($title);

    /**
     * @return mixed
     */
    public function getTitle();

    /**
     * @param $manufacturer
     * @return mixed
     */
    public function setManufacturer($manufacturer);

    /**
     * @return mixed
     */
    public function getManufacturer();

    /**
     * @param $description
     * @return mixed
     */
    public function setDescription($description);

    /**
     * @return mixed
     */
    public function getDescription();

    /**
     * @param $response
     * @return mixed
     */
    public function setResponse($response);

    /**
     * @return mixed
     */
    public function getResponse();

    /**
     * @param $relationId
     * @return mixed
     */
    public function setRelationId($relationId);

    /**
     * @return mixed
     */
    public function getRelationId();

    /**
     * @param $identifierType
     * @param $identifier
     * @return mixed
     */
    public function setIdentifier($identifierType, $identifier);

    /**
     * @param $marketplaceId
     * @return mixed
     */
    public function setMarketplaceId($marketplaceId);

    /**
     * @return mixed
     */
    public function getMarketplaceId();

    /**
     * @param $image
     * @return $this
     */
    public function setImage($image);

    /**
     * @return mixed
     */
    public function getImage();
}
