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
 * @package   Ced_GXpress
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Model\Source;

class Productavailability extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const STOCKIN = 'in stock';
    const STOCKOUT = 'out of stock';
    const PREORDER = 'preorder';

    public function getAllOptions()
    {
        return array(
            array(
                'value' => self::STOCKIN,
                'label' => __('Stock in')
            ),
            array(
                'value' => self::STOCKOUT,
                'label' => __('Out of Stock')
            ),
            array(
                'value' => self::PREORDER,
                'label' => __('Pre Order')
            )
        );
    }
}