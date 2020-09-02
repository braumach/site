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

class Productcondition extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const NEW_PRODUCT = 'new';
    const REFURBISHED_PRODUCT = 'refurbished';
    const USED_PRODUCT = 'used';

    public function getAllOptions()
    {
        return array(
            array(
                'value' => __(''),
                'label' => __('')
            ),
            array(
                'value' => self::NEW_PRODUCT,
                'label' => __('NEW')
            ),
            array(
                'value' => self::REFURBISHED_PRODUCT,
                'label' => __('REFURBISHED')
            ),
            array(
                'value' => self::USED_PRODUCT,
                'label' => __('USED')
            )
        );
    }
}