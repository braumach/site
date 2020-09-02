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
 * @package   Ced_MPCatch
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MPCatch\Model\Source\Feed;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Type
 *
 * @package Ced\MPCatch\Model\Source
 */
class Type extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => \CatchSdk\Core\Request::FEED_CODE_INVENTORY_UPDATE,
                'label' => __(\CatchSdk\Core\Request::FEED_CODE_INVENTORY_UPDATE),
            ],
            [
                'value' => \CatchSdk\Core\Request::FEED_CODE_ITEM_UPDATE,
                'label' => __(\CatchSdk\Core\Request::FEED_CODE_ITEM_UPDATE),
            ],
            [
                'value' => \CatchSdk\Core\Request::FEED_CODE_ITEM_DELETE,
                'label' => __(\CatchSdk\Core\Request::FEED_CODE_ITEM_DELETE),
            ],
            [
                'value' => \CatchSdk\Core\Request::FEED_CODE_ORDER_SHIPMENT,
                'label' => __(\CatchSdk\Core\Request::FEED_CODE_ORDER_SHIPMENT),
            ],
            [
                'value' => \CatchSdk\Core\Request::FEED_CODE_PRICE_UPDATE,
                'label' => __(\CatchSdk\Core\Request::FEED_CODE_PRICE_UPDATE),
            ],
            [
                'value' => \CatchSdk\Core\Request::FEED_CANCEL_ORDER_ITEM,
                'label' => __(\CatchSdk\Core\Request::FEED_CANCEL_ORDER_ITEM),
            ],
            [
                'value' => \CatchSdk\Core\Request::FEED_CODE_ORDER_CREATE,
                'label' => __(\CatchSdk\Core\Request::FEED_CODE_ORDER_CREATE),
            ]
        ];
    }
}
