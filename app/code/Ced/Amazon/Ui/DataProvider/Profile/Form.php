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

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class Form
 */
class Form extends AbstractDataProvider
{
    const NAMESPACE_VALUE = "amazon_profile_products";

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public $objectManager;

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

    /** @var \Ced\Amazon\Repository\Profile\Product  */
    public $product;

    /** @var \Ced\Amazon\Repository\Strategy\Assignment  */
    public $assignmentRepository;

    /** @var \Ced\Amazon\Helper\Config  */
    public $config;

    public $pool;

    /** @var array  */
    public $filter = [
        "selected" => [],
        "namespace" => self::NAMESPACE_VALUE
    ];

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory $collectionFactory,
        \Ced\Amazon\Repository\Profile\Product $product,
        \Ced\Amazon\Repository\Strategy\Assignment $assignmentRepository,
        \Ced\Amazon\Helper\Config $config,
        PoolInterface $pool,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->config = $config;
        $this->pool = $pool;
        $this->product = $product;
        $this->assignmentRepository = $assignmentRepository;
        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * Get data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->getData();

        $data = [];

        foreach ($items as $item) {
            $this->filter["selected"] = $this->product->getProductIdsByProfileId($item['id']);
            $item['filter'] = json_encode($this->filter);

            if (isset($item[\Ced\Amazon\Api\Data\ProfileInterface::COLUMN_STRATEGY_ASSIGNMENT]) &&
                $categoryId = $this->assignmentRepository
                    ->getCategoryIdByStrategyId($item[\Ced\Amazon\Api\Data\ProfileInterface::COLUMN_STRATEGY_ASSIGNMENT])) {
                $item['category_id'] = $categoryId;
            }

            $item['marketplace_disable'] =
                isset($item['marketplace']) && !empty($item['marketplace']) ? false : true;
            $item['profile_sub_category_disable'] =
                isset($item['profile_sub_category']) && !empty($item['profile_sub_category']) ? false : true;
            $item['profile_sub_category'] =
                isset($item['profile_sub_category'], $item['profile_category']) ?
                    $item['profile_category'] . "_" . $item['profile_sub_category'] : "";
            $data[$item['id']] = $item;
        }

        return $data;
    }
}
