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
 * Class GlobalShippingMethodList
 * @package Ced\GXpress\Block\Adminhtml\Config\Field
 */
class ImportGXpressAttribute extends \Magento\Framework\View\Element\Html\Select
{
    private $_gxpressAttribute;

    /**
     * GlobalShippingMethodList constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Ced\GXpress\Model\Config\InternationalShippingService $shipMethod
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    protected function _getGXpressAttribute()
    {
        if ($this->_gxpressAttribute === null) {
            $gxpressAttrs = array(
                array(
                    'value' => 'SKU',
                    'label' => 'SKU ( Custom Label )'
                ),
                array(
                    'value' => 'Title',
                    'label' => 'Title'
                )
            );

            $this->_gxpressAttribute = $gxpressAttrs;
        }
        return $this->_gxpressAttribute;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getGXpressAttribute() as $method) {
                $this->addOption($method['value'], addslashes($method['label']));
            }
        }
        return parent::_toHtml();
    }
}
