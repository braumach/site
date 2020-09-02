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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\ResourceModel\Profile;

use Magento\Framework\DB\Select;

/**
 * Class Product
 * @package Ced\Amazon\Model\ResourceModel\Profile
 * @method save(\Ced\Amazon\Api\Data\Profile\ProductInterface $object)
 * @method load(\Ced\Amazon\Api\Data\Profile\ProductInterface $object, $value, $field = null)
 */
class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const PAGE_SIZE = 1;

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Ced\Amazon\Model\Profile\Product::NAME,
            \Ced\Amazon\Model\Profile\Product::COLUMN_ID
        );
    }

    /**
     * Delete all relations with provided profile_id
     * @param $profileId
     * @return bool|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByProfileId($profileId)
    {
        $status = false;
        if ($profileId <= 0) {
            return $status;
        }

        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            [\Ced\Amazon\Model\Profile\Product::COLUMN_PROFILE_ID.'=?' => $profileId]
        );

        return $status;
    }

    /**
     * Check If Product Exist
     * @param $productId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkIfExists($productId)
    {
        $count = 0;

        try {
            $select = $this->getConnection()->select()
                ->from($this->getMainTable(), [])
                ->columns(['total' => 'COUNT(*)'])
                ->where(\Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID . '=?', $productId);
            $count = $this->getConnection()->fetchOne($select);
        } catch (\Exception $e) {
            // Silence
        }

        return (0 > $count);
    }

    /**
     * Get Product Ids by Profile Id
     * @param $profileId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductIdsByProfileId($profileId)
    {
        $products = [];
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), [])
            ->columns(['total' => 'COUNT(*)'])
            ->where(\Ced\Amazon\Model\Profile\Product::COLUMN_PROFILE_ID .'=?', $profileId);

        $total = $connection->fetchOne($select);

        if ($total > self::PAGE_SIZE) {
            $size = ceil($total/self::PAGE_SIZE);
            $page = 0;
            while ($page <= $size) {
                $select = $connection->select()->from(
                    $this->getMainTable(),
                    [\Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID]
                )
                    ->where(\Ced\Amazon\Model\Profile\Product::COLUMN_PROFILE_ID .'=?', $profileId)
                    ->limit(self::PAGE_SIZE, ($page*self::PAGE_SIZE));
                $result = $connection->query($select);
                while ($row = $result->fetch(\Zend_Db::FETCH_ASSOC)) {
                    array_push($products, $row['product_id']);
                }
                $page++;
            }
        } elseif ($total > 0) {
            $select = $connection->select()->from(
                $this->getMainTable(),
                [\Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID]
            )
                ->where(\Ced\Amazon\Model\Profile\Product::COLUMN_PROFILE_ID .'=?', $profileId);
            $result = $connection->query($select);
            while ($row = $result->fetch(\Zend_Db::FETCH_ASSOC)) {
                array_push($products, $row['product_id']);
            }
        }

        return $products;
    }

    /**
     * Delete by Product Ids and Profile Id
     * @param $productIds
     * @return bool|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByProductIdsAndProfileId($productIds, $profileId)
    {
        $status = false;
        if (empty($profileId) || empty($productIds) || !is_array($productIds)) {
            return $status;
        }

        $productIds = array_unique($productIds);
        $total = count($productIds);
        $connection = $this->getConnection();
        if ($total > self::PAGE_SIZE) {
            $chunks = array_chunk($productIds, self::PAGE_SIZE);
            foreach ($chunks as $chunk) {
                $status = $connection->delete(
                    $this->getMainTable(),
                    [
                        \Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID . ' IN (?)' => implode(',', $chunk),
                        \Ced\Amazon\Model\Profile\Product::COLUMN_PROFILE_ID . '=?' => $profileId
                    ]
                );
            }
        } else {
            $status = $connection->delete(
                $this->getMainTable(),
                [
                    \Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID . ' IN (?)' => implode(',', $productIds),
                    \Ced\Amazon\Model\Profile\Product::COLUMN_PROFILE_ID . '=?' => $profileId
                ]
            );
        }

        return $status;
    }

    public function addProductsIdsWithProfileId($productIds, $profileId)
    {
        $status = false;
        if (empty($productIds) || !is_array($productIds)) {
            return $status;
        }

        try {
            $productIds = array_unique($productIds);
            $data = [];
            foreach ($productIds as $productId) {
                $data[] = [
                    'profile_id' => (int)$profileId,
                    'product_id' => (int)$productId
                ];
            }
            $connection = $this->getConnection();
            $status = $connection->insertOnDuplicate($this->getMainTable(), $data);
        } catch (\Exception $e) {
            //TODO: add validation and continue for valid data.
            $status = false;
        }

        return $status;
    }

    /**
     * Get distinct profile ids by product ids
     * @param array $productIds
     * @return array
     * @throws \Exception
     */
    public function getProfileIdsByProductIds(array $productIds = [])
    {
        $profileIds = [];
        if (!empty($productIds) && is_array($productIds)) {
            $productIds = implode(',', $productIds);
            $mainTable = $this->getMainTable();
            $profileTable = $this->getTable(\Ced\Amazon\Model\Profile::NAME);
            $query = $this->getConnection()->select()
                ->from(
                    ['main_table' => $mainTable],
                    [
                        'main_table.id',
                        'main_table.product_id',
                        'main_table.profile_id',
                    ]
                )
                ->where(
                    "main_table.product_id IN ({$productIds})"
                );
            $query->group("main_table.profile_id");
            $query->join(
                ['profile' => $profileTable],
                "main_table.profile_id = profile.id",
                [
                    'profile.store_id as store_id',
                    'profile.profile_status as profile_status',
                ]
            )
                ->where("profile.profile_status = 1");

            $result = $this->getConnection()->query($query);
            while ($row = $result->fetch(\Zend_Db::FETCH_ASSOC)) {
                $profileIds[$row['store_id']][$row['profile_id']] = $row['profile_id'];
            }
        }
        return $profileIds;
    }
}
