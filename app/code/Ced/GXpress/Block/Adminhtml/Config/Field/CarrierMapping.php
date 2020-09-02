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

namespace Ced\GXpress\Block\Adminhtml\Config\Field;

/**
 * Class CarrierMapping
 * @package Ced\GXpress\Block\Adminhtml\Config\Field
 */
class CarrierMapping extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var
     */
    protected $_shippingRegion;

    /**
     * @var
     */
    protected $_shippingMethod;
    /**
     * @var
     */

    protected $_magentoAttr;
    /**
     * @var
     */
    protected $_enabledRenderer;
    /**
     * @var
     */
    protected $_gxpressCarrierRenderer;
    /**
     * @var
     */
    protected $_magentoCarrierRenderer;

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function _getGXpressCarrierRenderer()
    {
        if (!$this->_gxpressCarrierRenderer) {
            $this->_gxpressCarrierRenderer = $this->getLayout()->createBlock(
                'Ced\GXpress\Block\Adminhtml\Config\Field\CarrierMappingList',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_gxpressCarrierRenderer->setClass('gxpress_carrier_select');
            $this->_gxpressCarrierRenderer->setId('<%- _id %>');
        }
        return $this->_gxpressCarrierRenderer;
    }


    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getMagentoCarrierRenderer()
    {
        if (!$this->_magentoCarrierRenderer) {
            $this->_magentoCarrierRenderer = $this->getLayout()->createBlock(
                'Ced\GXpress\Block\Adminhtml\Config\Field\MagentoCarrierList',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_magentoCarrierRenderer->setClass('magento_carrier_select');
            $this->_magentoCarrierRenderer->setId('<%- _id %>');
        }
        return $this->_magentoCarrierRenderer;
    }

    protected function _prepareToRender()
    {
        $this->addColumn(
            'magento_carrier', array(
                'label' => __('Magento Carrier'),
                'renderer' => $this->_getMagentoCarrierRenderer(),
            )
        );
        $this->addColumn(
            'gxpress_carrier', array(
                'label' => __('gxpress Carrier'),
                'renderer' => $this->_getGXpressCarrierRenderer(),
            )
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Carrier');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];

        $optionExtraAttr['option_' . $this->_getMagentoCarrierRenderer()->calcOptionHash($row->getData('magento_carrier'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getGXpressCarrierRenderer()->calcOptionHash($row->getData('gxpress_carrier'))] = 'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}