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

namespace Ced\GXpress\Block\Adminhtml\Profile\Edit\Tab\Attribute;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Registry;

if (defined('DS') === false) define('DS', DIRECTORY_SEPARATOR);

/**
 * Class CategoryJs
 * @package Ced\GXpress\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class CategoryJs extends Widget implements RendererInterface
{
    /**
     * @var string
     */
    public $_template = 'profile/category_js.phtml';
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
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->_profile = $this->_coreRegistry->registry('current_profile');
        $this->directoryList = $directoryList;
        parent::__construct($context, $data);
    }

    /**
     * @param $categoryLevel
     * @return array
     */
    public function getLevel(/*$categoryLevel*/)
    {
        $option = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $catLevel = $objectManager->get('Ced\GXpress\Model\Category')->getCollection()->getData();//->addFieldToFilter('level', $categoryLevel)->getData();
        foreach ($catLevel as $value) {
            //if ($value['level'] == $categoryLevel) {
                $option[$value['level']][] = $value;
            //}
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
