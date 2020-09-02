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
 * @package     Ced_MPCatch
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MPCatch\Controller\Adminhtml\Order;

use Magento\Framework\Data\Argument\Interpreter\Constant;

class MassShip extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Authorization level of a basic admin session
     * @var Constant
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_MPCatch::mpcatch_orders';

    public $filter;

    public $orderManagement;

    public $order;

    public $catchOrders;

    public $orderHelper;

    /**
     * MassCancel constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Ced\MPCatch\Model\Orders $collection,
        \Ced\MPCatch\Helper\Order $orderHelper,
        \Ced\MPCatch\Helper\Logger $logger
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->orderManagement = $orderManagement;
        $this->order = $order;
        $this->catchOrders = $collection;
        $this->orderHelper = $orderHelper;
        $this->logger = $logger;
    }

    /**
     * Execute
     * @return  void
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->catchOrders->getCollection());
        $mpcatchOrders = $collection;

        if (count($mpcatchOrders) == 0) {
            $this->messageManager->addErrorMessage('No Orders To Ship.');
            $this->_redirect('mpcatch/order/index');
            return;
        } else {
            $counter = 0;
            foreach ($mpcatchOrders as $mpcatchOrder) {
                $magentoOrderId = $mpcatchOrder->getIncrementId();
                $this->order = $this->_objectManager->create('\Magento\Sales\Api\Data\OrderInterface');
                $order = $this->order->loadByIncrementId($magentoOrderId);
                if ($order->getStatus() == 'complete' || $order->getStatus() == 'Complete') {
                    $return = $this->shipment($order, $mpcatchOrder);
                    if ($return) {
                        $counter++;
                    }
                }
            }
            if ($counter) {
                $this->messageManager->addSuccessMessage($counter . ' Orders Shipment Successfull to MPCatch.com');
                $this->_redirect('mpcatch/order/index');
                return;
            } else {
                $this->messageManager->addErrorMessage('Orders Shipment Unsuccessfull.');
                $this->_redirect('mpcatch/order/index');
                return;
            }
        }

    }

    /**
     * Shipment
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function shipment($order = null, $mpcatchOrder = null)
    {
        $carrier_name = $carrier_code = $tracking_number = '';
        foreach ($order->getShipmentsCollection() as $shipment) {
            $alltrackback = $shipment->getAllTracks();
            foreach ($alltrackback as $track) {
                if ($track->getTrackNumber() != '') {
                    $tracking_number = $track->getTrackNumber();
                    $carrier_code = $track->getCarrierCode();
                    $carrier_name = $track->getTitle();
                    break;
                }
            }
        }

        try {
            $purchaseOrderId = $mpcatchOrder->getMpcatchOrderId();
            if (empty($purchaseOrderId)) {
                return false;
            }

            if ($tracking_number && $mpcatchOrder->getMpcatchOrderId()) {
                $shippingProvider = $this->orderHelper->getShipmentProviders();
                $providerCode = array_column($shippingProvider, 'code');
                $carrier_code = (in_array(strtoupper($carrier_code), $providerCode)) ? strtoupper($carrier_code) : '';
                $args = ['TrackingNumber' => $tracking_number, 'ShippingProvider' => strtoupper($carrier_code), 'order_id' => $mpcatchOrder->getMagentoOrderId(), 'MPCatchOrderID' => $mpcatchOrder->getMpcatchOrderId(), 'ShippingProviderName' => strtolower($carrier_name)];
                $response = $this->orderHelper->shipOrder($args);
                $this->logger->log('ERROR',json_encode($response));
                return $response;
            }
            return false;
        } catch (\Exception $e){
            $this->logger->log('ERROR',json_encode($e->getMessage()));
            return false;
        }
    }
}
