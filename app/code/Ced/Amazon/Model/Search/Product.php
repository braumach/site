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
 * @category Ced
 * @package Ced_Amazon
 * @author CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright © 2019 CedCommerce. All rights reserved.
 * @license EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Search;

use Magento\Framework\Model\AbstractModel;

class Product extends AbstractModel implements \Ced\Amazon\Api\Data\Search\ProductInterface
{
    const NAME = 'ced_amazon_search_product';

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Search\Product::class);
    }

    /**
     * @param $asin
     * @return mixed|void
     */
    public function setAsin($asin)
    {
        $this->setData(self::COLUMN_ASIN, $asin);
    }

    /**
     * @return mixed
     */
    public function getAsin()
    {
        return $this->getData(self::COLUMN_ASIN);
    }

    /**
     * @param $upc
     * @return mixed|void
     */
    public function setUpc($upc)
    {
        $this->setData(self::COLUMN_UPC, $upc);
    }

    /**
     * @return mixed
     */
    public function getUpc()
    {
        return $this->getData(self::COLUMN_UPC);
    }

    /**
     * @param $gtin
     * @return mixed|void
     */
    public function setGtin($gtin)
    {
        $this->setData(self::COLUMN_GTIN, $gtin);
    }

    /**
     * @return mixed
     */
    public function getGtin()
    {
        return $this->getData(self::COLUMN_GTIN);
    }

    /**
     * @param $ean
     * @return mixed|void
     */
    public function setEan($ean)
    {
        $this->setData(self::COLUMN_EAN, $ean);
    }

    /**
     * @return mixed
     */
    public function getEan()
    {
        return $this->getData(self::COLUMN_EAN);
    }

    /**
     * @param $brand
     * @return mixed|void
     */
    public function setBrand($brand)
    {
        $this->setData(self::COLUMN_BRAND, $brand);
    }

    /**
     * @return mixed
     */
    public function getBrand()
    {
        return $this->getData(self::COLUMN_BRAND);
    }

    /**
     * @param $model
     * @return mixed|void
     */
    public function setModel($model)
    {
        $this->setData(self::COLUMN_MODEL, $model);
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->getData(self::COLUMN_MODEL);
    }

    /**
     * @param $title
     * @return mixed|void
     */
    public function setTitle($title)
    {
        $this->setData(self::COLUMN_TITLE, $title);
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getData(self::COLUMN_TITLE);
    }

    /**
     * @param $manufacturer
     * @return mixed|void
     */
    public function setManufacturer($manufacturer)
    {
        $this->setData(self::COLUMN_MANUFACTURER, $manufacturer);
    }

    /**
     * @return mixed
     */
    public function getManufacturer()
    {
        return $this->getData(self::COLUMN_MANUFACTURER);
    }

    /**
     * @param $description
     * @return mixed|void
     */
    public function setDescription($description)
    {
        $this->setData(self::COLUMN_DESCRIPTION, $description);
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->getData(self::COLUMN_DESCRIPTION);
    }

    /**
     * @param $response
     * @return mixed|void
     */
    public function setResponse($response)
    {
        $this->setData(self::COLUMN_RESPONSE, $response);
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->getData(self::COLUMN_RESPONSE);
    }

    /**
     * @param $relationId
     * @return mixed|void
     */
    public function setRelationId($relationId)
    {
        $this->setData(self::COLUMN_RELATION_ID, $relationId);
    }

    /**
     * @return mixed
     */
    public function getRelationId()
    {
        return $this->getData(self::COLUMN_RELATION_ID);
    }

    /**
     * @param $identifierType
     * @param $identifier
     * @return mixed|void
     */
    public function setIdentifier($identifierType, $identifier)
    {
        $this->setData(strtolower($identifierType), $identifier);
    }

    public function setMarketplaceId($marketplaceId)
    {
        $this->setData(self::COLUMN_MARKETPLACE_ID, $marketplaceId);
    }

    public function getMarketplaceId()
    {
        return $this->getData(self::COLUMN_MARKETPLACE_ID);
    }
    public function setImage($image)
    {
        $this->setData(self::COLUMN_IMAGE, $image);
    }

    public function getImage()
    {
        return $this->getData(self::COLUMN_IMAGE);
    }
}
