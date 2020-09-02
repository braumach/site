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

namespace Ced\GXpress\Helper;
/**
 * Class Data For GXpress Authenticated Seller Api
 * @package Ced\GXpress\Helper
 */
class ShipmentHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;
    /*
     * \Magento\Sales\Api\Data\OrderInterface
     */
    public $order;

    public $logger;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * Data constructor.
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\Registry $registry,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->logger = $this->objectManager->create('Ced\GXpress\Helper\Logger');
        $this->order = $order;
        $this->_coreRegistry = $registry;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->scopeConfigManager = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
    }

    public function execute()
    {
        $GXpressOrderCollection = $this->objectManager->create('\Ced\GXpress\Model\Orders')->getCollection()
            ->addFieldToFilter('status' , 'acknowledge')/*->getData()*/;

        foreach ($GXpressOrderCollection as $GXpressOrder) {
            $magentoOrderId = $GXpressOrder['magento_order_id'];
            $order = $this->objectManager->create('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($magentoOrderId);
            if($order->getStatus() == 'complete' || $order->getStatus() == 'Complete' ) {
                $accountId = $GXpressOrder->getAccountId();
                if ($this->_coreRegistry->registry('gxpress_account'))
                    $this->_coreRegistry->unregister('gxpress_account');
                $this->multiAccountHelper->getAccountRegistry($accountId);
                $this->objectManager->get('Ced\GXpress\Helper\Data')->updateAccountVariable();
                $return = $this->shipment($order,$GXpressOrder);
                $this->logger->addInfo('In Order Shipemnt Cron: '.$return, ['path' => __METHOD__]);
            }
        }
        return true;
    }

    /**
     * Shipment
     * @param $orders
     * @param $gxpressOrder
     * @return string
     */
    public function shipment($orders = null, $gxpressOrder = null)
    {
        $trackArray = [];
        foreach($orders->getShipmentsCollection() as $shipment) {
            $alltrackback = $shipment->getAllTracks();
            $order = $shipment->getOrder();
            foreach ($alltrackback as $track) {
                $trackArray = $track->getData();
                break;
            }
        }
        try{
            $incrementId = $gxpressOrder->getMagentoOrderId();
            $gxpressOrderId = $gxpressOrder->getGXpressOrderId();
            $shipTodatetime = strtotime(date('Y-m-d H:i:s'));
            $deliverydate = date("Y-m-d", $shipTodatetime) . 'T' . date("H:i:s", $shipTodatetime);

            $orderData = json_decode($gxpressOrder->getOrderData(), true);
            //after ack api end
            $trackNumber = "";
            if (isset($trackArray['track_number'])) {
                $trackNumber = (string)$trackArray['track_number'];
            }

            $shipStationcarrier = isset($trackArray['carrier_code']) ? $trackArray['carrier_code'] :
                $orderData->ShippingDetails->ShippingServiceOptions->ShippingService;

            $mappedShippingMethods = $this->scopeConfigManager->getValue('gxpress_config/gxpress_order/global_setting/carrier_mapping');
            if (!empty($mappedShippingMethods)) {
                if (strpos($mappedShippingMethods, 's:') !== false) {
                    $mappedShippingMethods = unserialize($mappedShippingMethods);
                } else {
                    $mappedShippingMethods = json_decode($mappedShippingMethods, true);
                }
            }
            if (is_array($mappedShippingMethods)) {
                $mappedShippingMethods = array_column($mappedShippingMethods, 'gxpress_carrier', 'magento_carrier');
            }
            $shippingCarrierUsed = isset($mappedShippingMethods[$shipStationcarrier]) ? $mappedShippingMethods[$shipStationcarrier] : '';

            $itemsData = [];
            foreach ($order->getAllVisibleItems() as $item) {
                $merchantSku = $item->getSku();
                $quantityOrdered = $item->getQtyOrdered();
                $quantityToShip = $item->getQtyShipped();
                $itemsData [] = [
                    'sku' => $merchantSku,
                    'req_qty'=> $quantityOrdered,
                    'ship_qty' => $quantityToShip,
                    'cancel_quantity' => 0
                ];
            }
            $shipData = [
                'ship_todate' => $shipTodatetime,
                'carrier' => $shippingCarrierUsed,
                'tracking' => $trackNumber,
                'items' => $itemsData
            ];
            if ($shipData) {
                $data = $this->objectManager->get('Ced\GXpress\Helper\Data')->createShipmentOrderBody(
                    $gxpressOrderId,
                    $trackNumber,
                    $shippingCarrierUsed,
                    $deliverydate,
                    true
                );
                if ($data == 'Success') {
                    $gxpressModel = $this->objectManager->get('Ced\GXpress\Model\Orders')->load($incrementId, 'magento_order_id');
                    $gxpressModel->setStatus('shipped');
                    $gxpressModel->setShipmentData(json_encode($shipData));
                    $gxpressModel->save();
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }  
        return "Shipped On gxpress Successfully";
    }
}
