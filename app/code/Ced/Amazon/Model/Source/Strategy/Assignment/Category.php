<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source\Strategy\Assignment;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Serialize\SerializerInterface;

class Category extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const CATEGORY_CACHE_IDENTIFIER = "AMAZON_CATALOG_PRODUCT_CATEGORY_TREE";

    const CATEGORY_EMPTY = "0";

    /** @var CacheInterface */
    public $cache;

    /** @var SerializerInterface */
    public $serializer;

    /** @var LocatorInterface  */
    public $locator;

    /** @var CategoryCollectionFactory */
    public $categoryCollectionFactory;

    public function __construct(
        CacheInterface $cache,
        SerializerInterface $serializer,
        LocatorInterface $locator,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->cache = $cache;
        $this->locator = $locator;
        $this->serializer = $serializer;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function getAllOptions()
    {
        $categoryTree = $this->cache->load(self::CATEGORY_CACHE_IDENTIFIER);
        if ($categoryTree) {
            return $this->serializer->unserialize($categoryTree);
        }

        // TODO: Use Profile/Strategy Store
        //$storeId = $this->locator->getStore()->getId();
        $storeId = 0;

        /** @var  $matchingNamesCollection */
        $matchingNamesCollection = $this->categoryCollectionFactory->create();
        $matchingNamesCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
            ->setStoreId($storeId);

        $shownCategoriesIds = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($matchingNamesCollection as $category) {
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }

        /** @var  $collection */
        $collection = $this->categoryCollectionFactory->create();

        $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
            ->addAttributeToSelect(['name', 'is_active', 'parent_id'])
            ->setStoreId($storeId);

        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value' => CategoryModel::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['value' => $categoryId];
                }
            }

            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
        }

        $this->cache->save(
            $this->serializer->serialize($categoryById[CategoryModel::TREE_ROOT_ID]['optgroup']),
            self::CATEGORY_CACHE_IDENTIFIER,
            [
                \Magento\Catalog\Model\Category::CACHE_TAG,
                \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
            ]
        );

        $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'][] = [
            'value' => self::CATEGORY_EMPTY,
            'is_active' => true,
            'label' => __("Select..."),
        ];

        return $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];
    }
}
