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

namespace Ced\GXpress\Block\Adminhtml\Order;

/**
 * Class ShipGXpressOrder
 * @package Ced\GXpress\Block\Adminhtml\Order
 */
class ShipGXpressOrder extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @return \Magento\Framework\App\ObjectManager
     */
    public function getObjectManager()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @param $helper
     * @return mixed
     */
    public function getHelper($helper)
    {
        $helper = $this->getObjectManager()->get("Ced\GXpress\Helper" . $helper);
        return $helper;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        $Incrementid = $this->getOrder()->getIncrementId();
        $resultdata = $this->getObjectManager()->get('Ced\GXpress\Model\Orders')->getCollection()->addFieldToFilter('magento_order_id', $Incrementid)->getFirstItem();

        return $resultdata;
    }


    /**
     * @param $resultdata
     */
    public function setOrderResult($resultdata)
    {
        return $this->_coreRegistry->register('current_jet_order', $resultdata);
    }


    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Ship GXpress Order');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Ship GXpress Order');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $data = $this->getModel();
        if (!empty($data->getData())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        $data = $this->getModel();
        if (!empty($data->getData())) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return string
     */
    public function getGXpressShipCarrier()
    {
        $shippingDetails = $this->getObjectManager()->get('Ced\GXpress\Helper\Data')->getShhipingDetails();
        $selectData = '<select id="carrier" class="admin__control-select" name="carrier" >';
        if (!empty($shippingDetails) && isset($shippingDetails['ShippingCarrierDetails'])) {
            foreach ($shippingDetails['ShippingCarrierDetails'] as $value) {
                if (isset($value['ShippingCarrier'])) {
                    $selectData = $selectData . '<option value ="' . $value['ShippingCarrier'] . '">' . $value['ShippingCarrier'] . '</option>';
                }
            }
        }
        $selectData = $selectData . '</select>';

        return $selectData;
    }
}
