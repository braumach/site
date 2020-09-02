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

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Profiles
 * @package Ced\GXpress\Model\Source
 */
class Profiles extends AbstractSource
{

    /**
     * @var profile
     * */
    public $profile;

    /**
     * Profiles constructor.
     * @param \Ced\GXpress\Model\Profile $profile
     */
    public function __construct(\Ced\GXpress\Model\Profile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [];
            $allProfiles = $this->profile->getCollection()
                            ->addFieldToFilter('profile_status', 1);
            $this->_options[] = [
                [
                    'label' => '  -  ',
                    'value' => '',
                ]
            ];
            foreach ($allProfiles as $profiles) {
                $this->_options[] = [
                                    'value' => $profiles->getId(),
                                    'label' => $profiles->getProfileName()
                                ];
            }
        }
        return $this->_options;
    }

    /**
     * @return mixed
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            if (isset($option['value'])) {
                $options[$option['value']] = (string)$option['label']. " [{$option['value']}]";
            }
        }
        return $options;
    }
}
