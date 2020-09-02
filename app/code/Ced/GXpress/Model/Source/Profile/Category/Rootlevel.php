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
 * @package     Ced_GXpress
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\GXpress\Model\Source\Profile\Category;

class Rootlevel implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Objet Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;

    /**
     * Constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem\DirectoryList $directoryList
    ) {
        $this->objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->directoryList = $directoryList;
    }

    /**
     * To Array
     * @return string|[]
     */
    public function toOptionArray()
    {
        $currentAccount = false;
        if($this->_coreRegistry->registry('gxpress_account'))
            $currentAccount = $this->_coreRegistry->registry('gxpress_account');
        $appPath = $this->directoryList->getRoot();
        $gxpressPath = $appPath . DS . "app" . DS . "code" . DS . "Ced" . DS . "GXpress" . DS . "Setup" . DS . "GXpressJson" . DS;
        $path = $gxpressPath . "gxpress_category.json";
        $rootlevel = $this->objectManager->get('Ced\GXpress\Helper\Data')->loadFile($path, '', '');
        $options = [];
        if (isset($rootlevel) && !empty($rootlevel)) {
            foreach ($rootlevel as $key => $value) {
                $options[$key] = [
                    'value'=>$value['csv_firstlevel_id'],
                    'label'=>$value['csv_firstlevel_id']
                ];

            }
        }
        $temp = array_unique(array_column($options, 'label'));

        foreach ($temp as $key => $value) {
            $option[] = [
                'value'=>$value,
                'label'=>$value
            ];

        }

        return $option;
    }

}