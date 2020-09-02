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

class Productagegroup extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const NEWBORN = 'newborn';
    const INFANT = 'infant';
    const TODDLER = 'toddler';
    const KIDS = 'kids';
    const ADULT = 'adult';

    public function getAllOptions()
    {
        return array(
            array(
                'value' => '',
                'label' => __('')
            ),
            array(
                'value' => self::NEWBORN,
                'label' => __('New Born')
            ),
            array(
                'value' => self::INFANT,
                'label' => __('Infant')
            ),
            array(
                'value' => self::TODDLER,
                'label' => __('Toddler')
            ),
            array(
                'value' => self::KIDS,
                'label' => __('Kids')
            ),
            array(
                'value' => self::ADULT,
                'label' => __('Adult')
            )
        );
    }
}