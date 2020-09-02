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

namespace Ced\Amazon\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Ced\Amazon\Model\ResourceModel\Profile\Product\CollectionFactory as AmazonProfileProductCollectionFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class Grid
 */
class Grid extends \Magento\Ui\DataProvider\AbstractDataProvider
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

    public $size;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection|void */
    public $collection;

    /** @var AmazonProfileProductCollectionFactory  */
    public $amazonProfileProductCollectionFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        AmazonProfileProductCollectionFactory $amazonProfileProductCollectionFactory,
        FilterBuilder $filterBuilder,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->amazonProfileProductCollectionFactory = $amazonProfileProductCollectionFactory;
        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;

        $filters = $request->getParam('filters', ['store_id' => 0]);
        // TODO: check for single store mode
        $storeId = isset($filters['store_id']) ? $filters['store_id'] : 0;

        $this->filterBuilder = $filterBuilder;
        $this->collection->setStoreId($storeId);

        // TODO: move to ced_amazon_product table
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PRODUCT_STATUS);
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_VALIDATION_ERRORS);
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_FEED_ERRORS);

        // Manually adding the field. Not required for qty field ?
        $this->addField(\Ced\Amazon\Api\Data\Profile\ProductInterface::ATTRIBUTE_CODE_PROFILE_ID);

        $this->addFilter($this->filterBuilder->setField('type_id')
            ->setConditionType('in')
            ->setValue(['simple', 'configurable','bundle'])
            ->create());
        $this->addFilter($this->filterBuilder->setField('visibility')
            ->setConditionType('in')
            ->setValue([1, 2, 3, 4])
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData()
    {
        \Magento\Framework\Profiler::start('amazon-product-grid');

        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        // Adding Amazon Product Data
        $this->addAdditionalData($this->getCollection());

        $data = parent::getData();

        if ($this->size === null) {
            // Hacking the collection size
            $collection = $this->getCollection();
            $sql = $collection->getSelectCountSql();
            $sql->reset(\Magento\Framework\DB\Select::GROUP);
            $this->size = $collection->getConnection()->fetchOne($sql, []);
        }

        \Magento\Framework\Profiler::stop('amazon-product-grid');

        return [
            'totalRecords' => $this->size,
            'items' => array_values($data),
        ];
    }

    /**
     * Add Amazon Data
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     */
    private function addAdditionalData($collection)
    {
        $tmp = [];

        $ids = $collection->getColumnValues('entity_id');

        /** @var \Ced\Amazon\Model\ResourceModel\Profile\Product\Collection $amazonProfileProductCollection */
        $amazonProfileProductCollection = $this->amazonProfileProductCollectionFactory->create();
        $alias = "cap";
        $name = $amazonProfileProductCollection->getResource()->getTable(\Ced\Amazon\Model\Product::NAME);
        $profileTableName = $amazonProfileProductCollection->getResource()->getTable( \Ced\Amazon\Model\Profile::NAME);
        if(!empty($ids)){
        $amazonProfileProductCollection
            ->getSelect()
            ->where("main_table.".\Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID." in (".implode(",", $ids).")")
            ->joinLeft(
                [$alias => $name],
                $alias.".".\Ced\Amazon\Model\Product::COLUMN_RELATION_ID.'=main_table.id',
                [
                    "cap.id as cap_id",
                    "cap.asin as cap_asin",
                    "cap.status as cap_status",
                    "cap.validation_errors as cap_validation_errors",
                    "cap.feed_errors as cap_feed_errors",
                    "cap.title_flag as cap_title_flag",
                    "cap.brand_flag as cap_brand_flag",
                    "cap.manufacturer_flag as cap_manufacturer_flag",
                    "cap.auto_assigned_flag as cap_auto_assigned_flag",
                    "cap.manually_assigned_flag as cap_manually_assigned_flag",
                ]
            )
            ->joinLeft(
                ['capf' => $profileTableName],
                "capf.".\Ced\Amazon\Model\Profile::COLUMN_ID.'=main_table.profile_id',
                [
                    "capf.profile_name as capf_profile_name",
                    "capf.profile_category as capf_profile_category",
                    "capf.profile_sub_category as capf_profile_sub_category",
                    "capf.marketplace as capf_marketplace",
                    "capf.account_id as capf_account_id",
                    "capf.store_id as capf_store_id",
                ]
            );
        }

        /** @var \Ced\Amazon\Model\Product $item */
        foreach ($amazonProfileProductCollection->getItems() as $item) {
            $tmp[$item->getData(\Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID)][] = $item->getData();
        }

        foreach ($collection->getItems() as $item) {
            if (isset($tmp[$item->getId()])) {
                $item->setData('ced_amazon_additional_data', $tmp[$item->getId()]);
            } else {
                $item->setData('ced_amazon_additional_data', []);
            }
        }
    }
}
