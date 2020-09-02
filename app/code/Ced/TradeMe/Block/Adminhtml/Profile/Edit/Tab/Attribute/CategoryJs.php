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
 * @package   Ced_TradeMe
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\TradeMe\Block\Adminhtml\Profile\Edit\Tab\Attribute;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Registry;

/**
 * Class CategoryJs
 * @package Ced\TradeMe\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class CategoryJs extends Widget implements RendererInterface
{

    /**
     * @var mixed
     */
    public $_profile;

    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;

    /**
     * CategoryJs constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->_profile = $this->_coreRegistry->registry('current_profile');
        parent::__construct($context, $data);
    }

    /**
     * @param $level
     * @return array
     */
    public function getLevel($level)
    {
        $option = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currentAccount = false;
        if($this->_coreRegistry->registry('trademe_account'))
            $currentAccount = $this->_coreRegistry->registry('trademe_account');

        $mediaDirectory = $objectManager->get('\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $folderPath = $mediaDirectory->getAbsolutePath('ced/trademe/');
        $path = $folderPath . $currentAccount . '/categoryLevel-' . $level . '.json';
        $rootlevel = $objectManager->get('Ced\TradeMe\Helper\Data')->loadFile($path);
        $options = isset($rootlevel['CategoryArray']['Category']) ? $rootlevel['CategoryArray']['Category'] : [];
        foreach ($options as $value) {
            if ($value['CategoryLevel'] == $level) {
                $option[] = $value;
            }
        }
        return $option;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}