<?php

namespace Ced\Amazon\Ui\DataProvider\Product\Search;

use Ced\Amazon\Model\ResourceModel\Search\Product\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Grid extends AbstractDataProvider
{
    /**
     * @var   \Ced\Amazon\Model\ResourceModel\Search\Product\Collection
     **/
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * Construct
     *
     * @param string $name Component name
     * @param string $primaryFieldName Primary field Name
     * @param string $requestFieldName Request field name
     * @param CollectionFactory $collectionFactory The collection factory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies Add field Strategy
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies Add filter Strategy
     * @param array $meta Component Meta
     * @param array $data Component extra data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        return $this->getCollection()->toArray();
    }

    /**
     * Add field to select
     *
     * @param string|array $field The field
     * @param string|null $alias Alias for the field
     *
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
            return;
        }
        parent::addField($field, $alias);
    }

    /**
     * {@inheritdoc}
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
            return;
        }
        parent::addFilter($filter);
    }
}