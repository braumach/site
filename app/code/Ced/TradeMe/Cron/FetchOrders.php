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
 * @package   Ced_TradeMe
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\TradeMe\Cron;

use Magento\Catalog\Model\Product;
use Ced\TradeMe\Helper\Data;


/**
 * Class SyncProducts
 * @package Ced\TradeMe\Cron
 */
class FetchOrders
{
    public $logger;

    public $orderHelper;

    /**
     * @var \Ced\TradeMe\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    public $collectionFactory;
    protected $_coreRegistry;
    public $dataHelper;


    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Ced\TradeMe\Helper\Order $orderHelper
     */
    public function __construct(
        Data $dataHelper,
        \Ced\TradeMe\Helper\Logger $logger,
        \Ced\TradeMe\Helper\Order $orderHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\TradeMe\Helper\TradeMe $trademe,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper
    )
    {
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
        $this->orderHelper = $orderHelper;
        $this->trademe = $trademe;
        $this->_coreRegistry = $coreRegistry;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Ced\TradeMe\Helper\Order
     */
    public function execute()
    {
        try {
            $this->logger->addInfo('In FetchOrders');
            $resultData = true;
            $scopeConfigManager = $this->objectManager
                ->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $autoFetch = $scopeConfigManager->getValue('trademe_config/trademe_cron/order_cron');
            if ($autoFetch) {
                $acccounts = $this->multiAccountHelper->getAllAccounts(true);
                $acccountIds = $acccounts->getColumnValues('id');

                $resultData = $this->orderHelper->fetchOrders($acccountIds);
                if (isset($resultData['error'])) {
                    $this->logger->addError('In FetchOrder Cron: error', ['path' => __METHOD__, 'Response' => json_encode($resultData)]);
                } else {
                    $this->logger->addError('In FetchOrder Cron: success', ['path' => __METHOD__]);
                }

                }

            $this->logger->addError('In FetchOrders Cron: Disable', ['path' => __METHOD__]);
            return $resultData;
        } catch (\Exception $e) {
            $this->logger->addError('In FetchOrders Cron: Exception', ['path' => __METHOD__, 'Response' => $e->getMessage()]);
        }
    }
}
