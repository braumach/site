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

namespace Ced\Amazon\Model\Source\Profile;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Type
 * @package Ced\Amazon\Model\Source
 */
class Type extends AbstractSource
{
    const TYPE_THIRD_PARTY_LISTING = '3rd_party';
    const TYPE_NEW_PRODUCT_UPLOAD = 'new_product';
    const TYPE_SEARCH_PRODUCT_UPLOAD = 'search_product';
    const TYPE_AUTO = 'auto_product';

    const AVAILABLE_TYPES = [
        self::TYPE_THIRD_PARTY_LISTING,
        self::TYPE_NEW_PRODUCT_UPLOAD,
        self::TYPE_SEARCH_PRODUCT_UPLOAD,
        self::TYPE_AUTO,
    ];

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::TYPE_THIRD_PARTY_LISTING,
                'label' => __('3rd Party Listing'),
            ],
            [
                'value' => self::TYPE_NEW_PRODUCT_UPLOAD,
                'label' => __('New Product Upload'),
            ],
            [
                'value' => self::TYPE_SEARCH_PRODUCT_UPLOAD,
                'label' => __('Search and Upload'),
            ],
            [
                'value' => self::TYPE_AUTO,
                'label' => __('Automatic Search & Upload'),
            ],
        ];
    }
}
