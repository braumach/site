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

namespace Ced\Amazon\Model\Source\Shipment;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Barcode
 * @package Ced\Amazon\Model\Source
 */
class AmazonShippingLevelCategory extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => 'all',
                'label' => __('All')
            ],
            [
                'value' => 'Expedited',
                'label' => __('Expedited'),
            ],
            [
                'value' => 'FreeEconomy',
                'label' => __('FreeEconomy'),
            ],
            [
                'value' => 'NextDay',
                'label' => __('NextDay'),
            ],
            [
                'value' => 'SameDay',
                'label' => __('SameDay'),
            ],
            [
                'value' => 'SecondDay',
                'label' => __('SecondDay'),
            ],
            [
                'value' => 'Scheduled',
                'label' => __('Scheduled'),
            ],
            [
                'value' => 'Standard',
                'label' => __('Standard')
            ]
        ];
    }
}
