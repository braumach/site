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
 * @package     Ced_Aramexshipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Aramexshipping\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Shipping\Block\Adminhtml\View as ShipmentView;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;

class View
{
  /** @var \Magento\Framework\UrlInterface */
  protected $_urlBuilder;

  /** @var \Magento\Framework\AuthorizationInterface */
  protected $_authorization;

  public function __construct(
    UrlInterface $url,
    AuthorizationInterface $authorization,
    \Magento\Framework\ObjectManagerInterface $objectInterface
  ) {
    $this->_urlBuilder = $url;
    $this->_authorization = $authorization;
    $this->_objectManager = $objectInterface;
  }

  public function beforeSetLayout(ShipmentView $view) {
    $shipment_id = $view->getRequest()->getParam('shipment_id');
    $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipment_id);
    $shipping_method = $shipment->getOrder()->getShippingMethod();
    if (strpos($shipping_method, 'aramexshipping') !== false) {
      $url = $this->_urlBuilder->getUrl('aramex/shipment/print', ['id' => $shipment_id]);

      $view->addButton(
        'aramex_label',
        [
          'label' => __('Aramex Label'),
          'class' => 'aramex-label',
          /*'onclick' => 'setLocation(\'' . $url . '\')'*/
          'target'  =>  '_blank',
          'onclick' => 'window.open(\'' . $url . '\')'
        ]
      );
    }
  }
}