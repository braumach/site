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

namespace Ced\Amazon\Model\Source\Strategy;

use Magento\Framework\Option\ArrayInterface;

class Inventory implements ArrayInterface
{
    /** @var \Ced\Amazon\Model\ResourceModel\Account\CollectionFactory */
    public $strategyCollectionFactory;

    public function __construct(
        \Ced\Amazon\Model\ResourceModel\Strategy\CollectionFactory $strategyCollectionFactory
    ) {
        $this->strategyCollectionFactory = $strategyCollectionFactory;
    }

    /*
     * Option getter
     * @return array
     */
    public function toOptionArray()
    {
        $accounts = $this->toArray();
        $result = [];

        foreach ($accounts as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $result;
    }

    /*
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {

        $strategies = $this->strategyCollectionFactory->create()
            ->addFieldToFilter(
                \Ced\Amazon\Api\Data\StrategyInterface::COLUMN_TYPE,
                ['in' => [
                    \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_INVENTORY,
                    \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_GLOBAL
                ]
                ]
            );

        $strategiesList = [];
        foreach ($strategies as $strategy) {
            $strategiesList[$strategy->getId()] = __($strategy->getName()) . ' | Id:' . $strategy->getId();
        }

        return $strategiesList;
    }
}
