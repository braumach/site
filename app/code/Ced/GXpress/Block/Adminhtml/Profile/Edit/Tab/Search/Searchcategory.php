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

namespace Ced\GXpress\Block\Adminhtml\Profile\Edit\Tab\Search;

if (defined('DS') === false) define('DS', DIRECTORY_SEPARATOR);

/**
 * Class Searchcategory
 * @package Ced\GXpress\Block\Adminhtml\Profile\Edit\Tab\Search
 */
class Searchcategory extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{

    /**
     * @var string
     */
    protected $_template = 'Ced_GXpress::profile/search/search_category.phtml';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected  $_objectManager;
    /**
     * @var \Magento\Framework\Registry
     */
    protected  $_coreRegistry;
    /**
     * @var mixed
     */
    protected  $_profile;
    /**
     * @var
     */
    protected  $_houzzAttribute;

    /**
     * Searchcategory constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->_profile = $this->_coreRegistry->registry('current_profile');
        $this->directoryList = $directoryList;
        parent::__construct($context, $data);
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

    /**
     * @param $level
     * @return array
     */
    public function getLevel($level)
    {
        $option = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /*$currentAccount = false;
        if($this->_coreRegistry->registry('gxpress_account'))
            $currentAccount = $this->_coreRegistry->registry('gxpress_account');*/
        $appPath = $this->directoryList->getRoot();
        $gxpressPath = $appPath . DS . "app" . DS . "code" . DS . "Ced" . DS . "GXpress" . DS . "Setup" . DS . "GXpressJson" . DS;
        $path = $gxpressPath . "gxpress_category.json";
        $rootlevel = $objectManager->get('Ced\GXpress\Helper\Data')->loadFile($path);
        foreach ($rootlevel as $key => $value) {
            if ($value['level'] == $level) {
                $option[] = $value;
            }
        }
        return $option;
    }
}
