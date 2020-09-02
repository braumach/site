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

namespace Ced\Amazon\Ui\DataProvider\Profile;

use Ced\Amazon\Model\ProfileProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Ced\Amazon\Model\Profile;

/**
 * TODO: recheck usability
 * Class Products
 */
class Products extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    public $addFieldStrategies;

    /**
     * @var array
     */
    public $addFilterStrategies;

    /**
     * @var FilterBuilder
     */
    public $filterBuilder;

    /**
     * @var Profile
     */
    public $profile;

    /**
     * @var \Magento\Ui\Model\Bookmark
     */
    public $bookmark;

    public $request;

    public $category;

    public $size;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        FilterBuilder $filterBuilder,
        \Ced\Amazon\Model\Source\Category $category,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;

        $this->category = $category;
        $this->filterBuilder = $filterBuilder;
        $this->collection = $collectionFactory->create();

        // Manually adding the field. Not required for qty field ?
        $this->addField(\Ced\Amazon\Api\Data\Profile\ProductInterface::ATTRIBUTE_CODE_PROFILE_ID);

        // TODO: move to ced_amazon_product table
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PRODUCT_STATUS);
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_VALIDATION_ERRORS);
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_FEED_ERRORS);

        $this->addFilter($this->filterBuilder->setField('type_id')
            ->setConditionType('in')
            ->setValue(['simple', 'configurable','bundle'])
            ->create());
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     * @return void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->getCollection();
        $collection->addCategoryIds();
        /** @var \Magento\Catalog\Model\Product $item */
        foreach ($collection as &$item) {
            $value = implode(",", $this->category->getCategoryNames($item->getCategoryIds()));
            $item['category'] = $value;
        }

        $items = $collection->toArray();

        if ($this->size === null) {
            // Hacking the collection size
            $collection = $this->getCollection();
            $sql = $collection->getSelectCountSql();
            $sql->reset(\Magento\Framework\DB\Select::GROUP);
            $this->size = $collection->getConnection()->fetchOne($sql, []);
        }

        return [
            'totalRecords' => $this->size,
            'items' => array_values($items),
        ];
    }

    public function getCategoryName($categoryId)
    {

    }
}
