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

/**
 * Class Error
 */
class Error extends \Magento\Ui\DataProvider\AbstractDataProvider
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
        $this->collection = $amazonProfileProductCollectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;

        $this->filterBuilder = $filterBuilder;
        // Adding Amazon Profile Data
        $this->addProfileData($this->getCollection());

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


    public function getData(){
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

       $items = $this->getCollection()->getData();
        return [
            'totalRecords' => count($items),
            'items' => $items,
        ];
    }

    /**
     * Add Amazon Data
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     */
    public function addProfileData($collection)
    {
        $magentoProductCollection = $collection->getResource()->getTable('catalog_product_entity');
        $amazonProfileProductCollection = $this->amazonProfileProductCollectionFactory->create();
        $amazonProductCollection = $amazonProfileProductCollection->getResource()->getTable(\Ced\Amazon\Model\Product::NAME);

        $collection->getSelect()
            ->joinLeft(
                ['mprod' => $magentoProductCollection],
                'main_table.'.\Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID.'= mprod.entity_id',
                [   'entity_id' => new \Zend_Db_Expr(
                    "mprod.entity_id"
                ), \Magento\Catalog\Model\Product::SKU => new \Zend_Db_Expr(
                    "mprod.".\Magento\Catalog\Model\Product::SKU
                ),\Magento\Catalog\Model\Product::TYPE_ID => new \Zend_Db_Expr(
                    "mprod.".\Magento\Catalog\Model\Product::TYPE_ID
                )
                ]
                )
            ->joinLeft(
                ['cap' => $amazonProductCollection],
                'cap.'.\Ced\Amazon\Model\Product::COLUMN_RELATION_ID.'=main_table.id',
                [
                    'account_id'=> new \Zend_Db_Expr(
                        "cap.account_id"
                    ), 'validation_errors'=> new \Zend_Db_Expr(
                        "cap.validation_errors"
                    )
//                    ,
//                    'feed_errors'=> new \Zend_Db_Expr(
//                        "cap.feed_errors"
//                    ),

                ]
            )
            ->where("cap.validation_errors IS NOT NULL")
        ->order('mprod.entity_id');

    }
}
