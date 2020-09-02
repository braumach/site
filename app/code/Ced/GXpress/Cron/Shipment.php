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

namespace Ced\GXpress\Cron;

class Shipment
{
    public $logger;

    public $orderHelper;

    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    public $GXpresslib;

    public $_coreRegistry;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Ced\GXpress\Helper\Order $orderHelper
     */
    public function __construct(
        \Ced\GXpress\Helper\Logger $logger,
        \Ced\GXpress\Helper\Order $orderHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        \Ced\GXpress\Helper\GXpresslib $GXpresslib,
        \Magento\Framework\Registry $registry
    )
    {
        $this->logger = $logger;
        $this->orderHelper = $orderHelper;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->GXpresslib = $GXpresslib;
        $this->_coreRegistry = $registry;
    }

    /**
     * @return array|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $order = true;
        $trackArray = array();
        $scopeConfigManager = $this->objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $autoShip = $scopeConfigManager->getValue('gxpress_config/gxpress_cron/shipment_cron');
        if ($autoShip) {
            $acccounts = $this->multiAccountHelper->getAllAccounts(true);
            $acccountIds = $acccounts->getColumnValues('id');
            foreach ($acccountIds as $acccountId) {

                if ($this->_coreRegistry->registry('gxpress_account'))
                    $this->_coreRegistry->unregister('gxpress_account');

                $account = $this->multiAccountHelper->getAccountRegistry($acccountId);
                $accountStatus = $account->getData('account_status');
                $accountToken = $account->getData('account_token');
                if ($accountStatus && $accountToken) {
                    $orderCollection = $this->objectManager->create('Ced\GXpress\Model\Orders')->getCollection()
                        ->addFieldToFilter('account_id', ['eq' => $acccountId]);//->addFieldToFilter('status', ['in' => array('pendingShipment', 'inProgress')]);
                    if (isset($orderCollection) && $orderCollection->getSize() > 0) {
                        foreach ($orderCollection as $item) {

                            $orderId = $item->getData('magento_id');
                            $gxpressOrderId = $item->getData('gxpress_order_id');
                            if($gxpressOrderId != 'G-SHP-6399-44-9018') {
                                continue;
                            }
                            $order = $this->objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
                            $orderStatus = $order->getData('status');
                            $magentoOrderStatus = array('complete');

                            if ($orderId && in_array($orderStatus, $magentoOrderStatus)) {
                                $orderData = json_decode($item->getOrderData(), true);
//                                $methodCode = $order->getShippingMethod();

                                foreach($order->getShipmentsCollection() as $shipment)
                                {
                                    $alltrackback = $shipment->getAllTracks();
                                    foreach ($alltrackback as $track) {
                                        if($track->getTrackNumber() != '') {
                                            $trackArray['track_number'] = $track->getTrackNumber();
                                            $trackArray['title'] = $track->getTitle();
                                            $trackArray['carrier_code'] = $track->getCarrierCode();
                                            break;
                                        }
                                    }
                                }

                                foreach ($order->getAllVisibleItems() as $key => $item) {
                                    /*$merchantSku = $item->getSku();
                                    $quantityToShip = $item->getQtyShipped();
                                    $quantityCanceled = $item->getQtyCanceled();*/

                                    $k = 0;
                                    $time = time() + ($k + 1);
                                    $shpId = implode("-", str_split($time, 3));
                                    $itemsData [$key] = [
                                        'shipment_item_id' => $shpId,
                                    ];

                                    $dataShip = array();
                                    /** TODO Handle Partial Shipments */

                                    $dataShip['shipments'][] = array(
                                        'purchaseOrderId' => $gxpressOrderId,
                                        'shipment_tracking_number' => isset($trackArray['track_number']) ? $trackArray['track_number'] : '',
                                        'carrier_name' => isset($trackArray['title']) ? $trackArray['title'] : '',
                                        'carrier' => isset($trackArray['carrier_code']) ? $trackArray['carrier_code'] : '',
                                        'shipment_tracking_url' => isset($trackArray['carrier_code'])
                                            ? 'www.' . strtolower($trackArray['carrier_code']) . '.com' : '',
                                        'shipment_items' => $itemsData
                                    );
                                }

                                if ($dataShip) {
                                    $dataShip['noCallToGenerateShipment'] = '1';
                                    $data = $this->GXpresslib->updateOrderStatus($dataShip);
                                    if (!is_bool($data)) {
                                        if ($data->getexecutionStatus() == 'executed'
                                            || $data->getexecutionStatus() == 'duplicate') {

                                            $gxpressModel = $this->objectManager
                                                ->create('Ced\GXpress\Model\Orders')->load($orderId, 'magento_id');

                                            $gxpressModel->setStatus('shipped');
                                            $gxpressModel->setShipmentData(json_encode($dataShip));
                                            $gxpressModel->save();
                                        }
                                    }

                                    $this->logger->addInfo('In ShipOrder Cron: success', ['path' => __METHOD__, 'account_id' => $acccountId, 'Response' => $dataShip]);
                                }
                            }
                        }
                    }

                }
            }
            return $order;
        }
        $this->logger->addError('In ShipOrder Cron: Disable', ['path' => __METHOD__]);
        return $order;
    }
}