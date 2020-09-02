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
 * @package     Ced_TradeMe
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\TradeMe\Model\Source\Profile\Category;

use Ced\TradeMe\Model\CategoryFactory;

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
    public $categoryFactory;

    /**
     * Constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\TradeMe\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * To Array
     * @return string|[]
     */
    public function toOptionArray()
    {
        $currentAccount = false;
        if($this->_coreRegistry->registry('trademe_account'))
            $currentAccount = $this->_coreRegistry->registry('trademe_account');

        $mediaDirectory = $this->objectManager->get('\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $folderPath = $mediaDirectory->getAbsolutePath('ced/trademe/');

        $path = $folderPath .$currentAccount->getAccountCode(). '/categories.json';
        $rootlevel = $this->objectManager->get('Ced\TradeMe\Helper\Data')->loadFile($path, '', '');
        $collection = $this->categoryFactory->create()->getCollection()
            ->addFieldToFilter('is_leaf', 1);
        $options = [];
        $options[0] = [
            'value' => null,
            'label' => "Please Select Category"
        ];
        $data = [];
        if ($collection/*isset($rootlevel['Subcategories']*//*['Category']*/) {
            foreach ($collection/*$rootlevel['Subcategories']*//*['Category']*/ as $value) {

                $options[]=[
                    'value'=>$value['trademe_id'],
                    'label'=>$value['code']
                ];
            }
        }

        return $options;
    }

}