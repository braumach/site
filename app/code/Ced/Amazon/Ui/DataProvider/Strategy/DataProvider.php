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

namespace Ced\Amazon\Ui\DataProvider\Strategy;

use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    public $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    public $addFilterStrategies;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Ced\Amazon\Model\ResourceModel\Strategy\CollectionFactory $collectionFactory
     * @param array  $addFieldStrategies
     * @param array  $addFilterStrategies
     * @param array  $meta
     * @param array  $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Ced\Amazon\Model\ResourceModel\Strategy\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
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

        $items = $this->getCollection()->getData();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),

        ];
    }

    public function getSearchCriteria()
    {
        return $this->getCollection();
    }
}
