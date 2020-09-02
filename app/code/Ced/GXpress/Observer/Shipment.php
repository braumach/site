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

namespace Ced\GXpress\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Shipment implements ObserverInterface
{
    /**
     * Request
     * @var  \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    public $GXpresslib;

    public $logger;
    /**
     * Shipment constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        ScopeConfigInterface $storeManager,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        \Ced\GXpress\Helper\GXpresslib $GXpresslib,
        \Ced\GXpress\Helper\Logger $logger
    )
    {
        $this->request = $request;
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->scopeConfigManager = $storeManager;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->GXpresslib = $GXpresslib;
        $this->logger = $logger;
    }

    /**
     * Product SKU Change event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(Observer $observer)
    {
        try {
            if($observer->getEvent()->getTrack()){
                $track = $observer->getEvent()->getTrack();
                $shipment = $track->getShipment();
            } elseif ($observer->getEvent()->getShipment()){
                $shipment = $observer->getEvent()->getShipment();
            }
            /*$track = $observer->getEvent()->getTrack();
            $shipment = $track->getShipment();*/

            $order = $shipment->getOrder();
            $shippingMethod = $order->getShippingMethod();
            $trackArray = [];

            foreach ($shipment->getAllTracks() as $tracks) {
                $trackArray = $tracks->getData();
            }
            
            if (empty($trackArray)) {
                return $observer;
            }
            $this->logger->addInfo("Google Express Tracking Details",$trackArray);
            $datahelper = $this->objectManager->get('Ced\GXpress\Helper\Data');
            $incrementId = $order->getIncrementId();
            $gxpressOrder = $this->objectManager->get('Ced\GXpress\Model\Orders')->load($incrementId, 'magento_order_id');
            $gxpressOrderId = $gxpressOrder->getGxpressOrderId();
            $accountId = $gxpressOrder->getAccountId();

            if ($this->registry->registry('gxpress_account')) {
                $this->registry->unregister('gxpress_account');
            }
            $this->multiAccountHelper->getAccountRegistry($accountId);
            $datahelper->updateAccountVariable();
            
            if (empty($gxpressOrderId)) {
                return $observer;
            }

            if ($gxpressOrder->getGxpressOrderId()) {
            
                $offset = '.0000000-00:00';
                $shipTodatetime = strtotime(date('Y-m-d H:i:s'));
                $carrtime = strtotime((string)date('Y-m-d H:i:s'));
                $deliverydate = date("Y-m-d", $shipTodatetime) . 'T' . date("H:i:s", $shipTodatetime);

                $shipToDate = date("Y-m-d", $shipTodatetime) . 'T' . date("H:i:s", $shipTodatetime) . $offset;
                $carrierPickdate = date("Y-m-d", $carrtime) . 'T' . date("H:i:s", $carrtime) . $offset;

                $orderData = json_decode($gxpressOrder->getOrderData(), true);
                $methodCode = (string)$orderData['shippingOption'];
                $methodCode = empty($methodCode) ? 'Standard' : $methodCode;
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
                foreach ($order->getAllVisibleItems() as $key => $item) {
                    $merchantSku = $item->getSku();
                    $quantityOrdered = $item->getQtyOrdered();
                    $quantityToShip = $item->getQtyShipped();
                    $quantityCanceled = $item->getQtyCanceled();

                    $k = 0;
                    $time = time() + ($k + 1);
                    $shpId = implode("-", str_split($time, 3));

                    $lineNumber = isset($orderData[$key]['product']['offerId']) ? $orderData[$key]['product']['offerId'] : 'no_linenumber';

                    if ($lineNumber == 'no_linenumber' && false) {
                        continue;
                    }

                    $itemsData [$key] = [
                        'lineNumber' => $lineNumber,
                        'shipment_item_id' => $shpId,
                        'merchant_sku' => $merchantSku,
                        'response_shipment_sku_quantity' => intval($quantityToShip),
                        'cancel_quantity' => intval($quantityCanceled)
                    ];

                    $dataShip = array();

                    $dataShip['shipments'][] = array(
                        'purchaseOrderId' => $gxpressOrderId,
                        'shipment_tracking_number' => $trackNumber,
                        'response_shipment_date' => $shipToDate,
                        'carrier_pick_up_date' => $carrierPickdate,
                        'carrier_name' => isset($trackArray['title']) ? $trackArray['title'] : '',
                        'carrier' => isset($trackArray['carrier_code']) ? $trackArray['carrier_code'] : '',
                        'shipment_tracking_url' => isset($trackArray['carrier_code'])
                            ? 'www.' . strtolower($trackArray['carrier_code']) . '.com' : '',
                        'methodCode' => $methodCode,
                        'shipment_items' => $itemsData
                    );
                }
                $this->logger->addInfo("Google Express Log Info 1",$dataShip);
                if ($dataShip) {
                    $dataShip['noCallToGenerateShipment'] = '1';
                    $data = $this->GXpresslib->updateOrderStatus($dataShip);
                    /*$data = new \Google_Service_ShoppingContent_OrdersShipLineItemsResponse();
                    $data->setExecutionStatus("executed");
                    $data->setKind("content#ordersShipLineItemsResponse");*/

                    if (!is_bool($data)) {
                        if ($data->getexecutionStatus() == 'executed'
                            || $data->getexecutionStatus() == 'duplicate') {

                            $gxpressModel = $this->objectManager
                                ->get('Ced\GXpress\Model\Orders')->load($incrementId, 'magento_order_id');

                            $gxpressModel->setStatus('shipped');
                            $gxpressModel->setShipmentData(json_encode($dataShip));
                            $gxpressModel->save();

                        }
                    }

                }
            }
        } catch (\Exception $e) {
            $this->logger->addError("Google Express Log Error",array($e->getMessage()));
            return $observer;
        }
        return $observer;
    }
}
