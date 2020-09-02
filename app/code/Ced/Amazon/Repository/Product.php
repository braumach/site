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

namespace Ced\Amazon\Repository;

use Ced\Amazon\Api\Data\ProductInterface;
use Ced\Amazon\Api\Data\Profile\ProductInterface as ProfileProductInterface;
use Ced\Amazon\Model\Cache;
use Ced\Amazon\Model\ProductFactory;
use Ced\Amazon\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\AlreadyExistsException;

class Product implements \Ced\Amazon\Api\ProductRepositoryInterface
{
    const CACHE_IDENTIFIER = "product_table_";

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Product
     */
    private $amazonResource;

    /**
     * @var ProductFactory
     */
    private $amazonProductFactory;
    private $amazonCollectionFactory;
    private $amazonProfileProductResource;
    private $amazonProfileProductCollection;

    public function __construct(
        Cache $cache,
        \Ced\Amazon\Model\ResourceModel\Product $amazonResource,
        CollectionFactory $amazonCollectionFactory,
        ProductFactory $amazonProductFactory,
        \Ced\Amazon\Model\ResourceModel\Profile\Product $profileProductResource,
        \Ced\Amazon\Model\ResourceModel\Profile\Product\CollectionFactory $profileProductCollection
    ) {
        $this->cache = $cache;
        $this->amazonProductFactory = $amazonProductFactory;
        $this->amazonResource = $amazonResource;
        $this->amazonCollectionFactory = $amazonCollectionFactory;
        $this->amazonProfileProductResource = $profileProductResource;
        $this->amazonProfileProductCollection = $profileProductCollection;
    }

    /**
     * @param $relationId
     * @return \Ced\Amazon\Model\Product
     */
    public function getByRelationId($relationId)
    {
        $product = $this->amazonProductFactory->create();
        $this->amazonResource->load($product, $relationId, ProductInterface::COLUMN_RELATION_ID);
        if (!$product->getId() > 0) {
            $product->setRelationId($relationId);
        }

        return $product;
    }

    /**
     * @param $productID
     * @param $profileID
     * @return \Ced\Amazon\Model\ResourceModel\Profile\Product\Collection
     */
    public function getRelationId($productID, $profileID)
    {
        $collection = $this->amazonProfileProductCollection->create();
        $relationID=$collection
            ->addFieldToFilter('product_id', $productID)
            ->addFieldToFilter('profile_id', $profileID)
            ->addFieldToSelect('id');

        return $relationID->getData()[0]['id'];
    }

    /**
     * @param array $productIds
     * @param $profileId
     * @return bool
     */
    public function addRelationIdByProfileId(array $productIds, $profileId)
    {
        $collection = $this->amazonProfileProductCollection->create();
        $profileTableName = $collection->getTable(\Ced\Amazon\Model\Profile::NAME);
        $collection->addFieldToFilter(
            ['id as relation_id', 'product_id', 'profile_id'],
            [
                ['in' => $productIds],
                ['eq' => $profileId]
            ]
        )->join(
            $profileTableName,
            $profileTableName . "." . \Ced\Amazon\Model\Profile::COLUMN_ID . "= main_table." . \Ced\Amazon\Model\Profile\Product::COLUMN_PROFILE_ID,
            \Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE
        )->addFieldToSelect('id');
        /** @var ProfileProductInterface $product */
        foreach ($collection as $product) {
            /** @var ProductInterface $amazonProduct */
            $amazonProduct = $this->amazonProductFactory->create();
            $amazonProduct->setRelationId($product->getRelationId());
            $amazonProduct->setMarketplaceId($product->getMarketplace());
            try {
                $this->amazonResource->save($amazonProduct);
            } catch (\Exception $e) {
                //TODO: add log
                var_dump($e->getMessage());
            }
        }
        return true;
    }

    /**
     * @param ProductInterface $product
     * @return int
     * @throws AlreadyExistsException
     */
    public function save(ProductInterface $product)
    {
        try {
            $this->amazonResource->save($product);
            return true;
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}
