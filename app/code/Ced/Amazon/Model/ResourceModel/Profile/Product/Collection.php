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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\ResourceModel\Profile\Product;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Ced\Amazon\Model\ResourceModel\Product
 */
class Collection extends AbstractCollection
{
    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    private $catalogCollectionFactory;

    /**
     * Collection constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollectionFactory
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollectionFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->catalogCollectionFactory = $catalogCollectionFactory;
    }

    public function _construct()
    {
        $this->_init(
            \Ced\Amazon\Model\Profile\Product::class,
            \Ced\Amazon\Model\ResourceModel\Profile\Product::class
        );
    }

    /**
     * @param $profileId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductsByProfileId($profileId)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $catalog */
        $productCollection = $this->catalogCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        $productCollection
            ->getSelect()
            ->join(
                ['capp' => $this->getMainTable()],
                'e.entity_id = capp.product_id',
                [
                    'capp.profile_id as capp_profile_id',
                ]
            )
            ->where('capp.profile_id=?', $profileId);
        return $productCollection;
    }

    protected function _initSelect()
    {
        $this->addFilterToMap(
            'type_id',
            'mprod.type_id'
        );
        $this->addFilterToMap(
            'entity_id',
            'mprod.entity_id'
        );
        parent::_initSelect();
    }
}
