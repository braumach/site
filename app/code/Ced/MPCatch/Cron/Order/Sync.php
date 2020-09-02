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
 * @package   Ced_MPCatch
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MPCatch\Cron\Order;

class Sync
{
    public $logger;

    /**
     * @var $config
     */
    public $config;

    /**
     * Import constructor.
     *
     * @param \Ced\MPCatch\Helper\Order  $order
     * @param \Ced\MPCatch\Helper\Logger $logger
     */
    public function __construct(
        \Ced\MPCatch\Helper\Order $order,
        \Ced\MPCatch\Helper\Logger $logger,
        \Ced\MPCatch\Model\Orders $collection,
        \Ced\MPCatch\Helper\Config $config
    ) {
        $this->order = $order;
        $this->logger = $logger;
        $this->orders = $collection;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        try {
            $orderSyncCron = $this->config->getOrderSyncCron();
            if ($orderSyncCron == '1') {
                $orderCollection = $this->orders->getCollection()
                    ->addFieldToFilter('status', array('in', array('WAITING_ACCEPTANCE', 'WAITING_DEBIT', 'WAITING_DEBIT_PAYMENT', 'SHIPPING', 'WAITING_DEBIT_PAYMEN')));
                $orderIds = $orderCollection->getColumnValues('mpcatch_order_id');
                $syncResponse = $this->order->syncOrders($orderIds);
                $this->logger->info('Order Sync Cron Response', ['path' => __METHOD__, 'OrderIds' => implode(',', $orderIds), 'OrderShipmentReponse' => var_export($syncResponse)]);
                return $syncResponse;
            } else {
                $this->logger->info('Order Sync Cron Disabled', ['path' => __METHOD__, 'Cron Status' => 'Disable']);
            }
            return false;
        } catch (\Exception $e){
            $this->logger->error('Order Sync Cron', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }
}
