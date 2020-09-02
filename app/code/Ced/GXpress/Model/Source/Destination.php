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

class Destination
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'Shopping',
                'label' => __('Shopping Ads')
            ),
            array(
                'value' => 'ShoppingActions',
                'label' => __('Shopping Actions')
            ),
            array(
                'value' => 'SurfacesAcrossGoogle',
                'label' => __('Surfaces across Google')
            )
        );
    }
}