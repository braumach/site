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

namespace Ced\Amazon\Model\ResourceModel;

/**
 * Class Strategy
 * @package Ced\Amazon\Model\ResourceModel
 * @method load(\Ced\Amazon\Api\Data\StrategyInterface $object, $value, $field = null)
 * @method save(\Ced\Amazon\Api\Data\StrategyInterface $object)
 */
class Strategy extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Ced\Amazon\Model\Strategy::NAME,
            \Ced\Amazon\Model\Strategy::COLUMN_ID
        );
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $field = $this->getConnection()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
        $globalStrategy = $this->getTable(\Ced\Amazon\Model\Strategy\GlobalStrategy::NAME);
        $select = $this->getConnection()->select()->from($this->getMainTable())->where($field . '=?', $value)
            ->joinLeft(
                $globalStrategy,
                $this->getMainTable().'.id = '.$globalStrategy.'.strategy_id',
                [
                    \Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_ID,
                    \Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_FULFILLMENT_LATENCY,
                    \Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD,
                    \Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD_LESS_THAN_VALUE,
                    \Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD_GREATER_THAN_VALUE,
                    \Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD_BREAKPOINT,
                    \Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_MERCHANT_SHIPPING_GROUP_NAME,
                ]
            );
        return $select;
    }
}
