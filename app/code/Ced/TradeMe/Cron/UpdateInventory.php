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
 * @package     Ced_TradeMe
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\TradeMe\Cron;

use Magento\Eav\Model\ResourceModel\Attribute\Collection;

class UpdateInventory
{
    /**
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * OM
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Config Manager
     * @var \Ced\TradeMe\Helper\Data
     */
    public $helper;

    /**
     * Config Manager
     * @var \Ced\TradeMe\Helper\TradeMe
     */
    public $trademeHelper;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * @var
     */
    public $helperData;
    /**
     * @var \Ced\TradeMe\Model\Productchange
     */
    public $productchange;

    /**
     * @var \Ced\TradeMe\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public $token;

    public $schedulerCollection;

    public $schedulerResource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Attribute\CollectionFactory
     */
    public $productCollectionFactory;

    /**
     * UploadProducts constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Ced\TradeMe\Helper\Logger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Ced\TradeMe\Model\ResourceModel\JobScheduler\CollectionFactory $schedulerCollection,
        \Ced\TradeMe\Model\JobScheduler $schedulerResource,
        \Ced\TradeMe\Helper\Data $helperData,
        \Ced\TradeMe\Helper\TradeMe $trademeHelper,
        \Ced\TradeMe\Model\Productchange $productchange,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Registry $registry
    )
    {
        $this->scopeConfigManager = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->objectManager = $objectManager;
        $this->helper = $this->objectManager->get('Ced\TradeMe\Helper\Data');
        $this->logger = $logger;
        $this->trademeHelper = $trademeHelper;
        $this->directoryList = $directoryList;
        $this->helperData = $helperData;
        $this->productchange = $productchange;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->_coreRegistry = $registry;
        $this->schedulerCollection = $schedulerCollection;
        $this->schedulerResource = $schedulerResource;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Execute
     * @return bool
     */
    public function execute()
    {
        $this->logger->addInfo('In UpdateInventory');
        $scopeConfigManager = $this->objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $autoSync = $scopeConfigManager->getValue('trademe_config/trademe_cron/inventory_cron');
        if ($autoSync) {
            $accountIds = $this->multiAccountHelper->getAllAccounts(true);
            foreach ($accountIds as $accountId) {
                $id = [];
                $collection = $this->productchange->getCollection();
                $type = \Ced\TradeMe\Model\Productchange::CRON_TYPE_INVENTORY;
                $collection->addFieldToFilter('cron_type', $type)
                    ->addFieldToFilter('threshold_limit', array('lt' => 2))
                    ->addFieldToFilter('account_id', array('eq' => $accountId->getId()));
                if (count($collection) > 0) {
                    $prodId = [];
                    foreach ($collection->getData() as $dataa) {
                        $prodId[] = $dataa['product_id'];
                    }
                    if ($this->_coreRegistry->registry('trademe_account'))
                        $this->_coreRegistry->unregister('trademe_account');
                    $this->multiAccountHelper->getAccountRegistry($accountId->getId());
                    $this->helperData->updateAccountVariable();

                    $id[$accountId->getId()] = $prodId;
                    try {
                        if (isset($id)) {

                            $product = $this->objectManager->get('Magento\Catalog\Model\Product')->load($id);
                            $imageid = $this->trademeHelper->imageData($product, $accountId->getId());

                            if (isset($imageid['error'])) {
                                $this->logger->addError('In SyncProducts Cron: error',
                                    ['path' => __METHOD__, 'account_id' => $accountId, 'Response' => "Error While Uploading Images for SKU: " . $product->getSku() . " " . $imageid['error']]);
                            }
                            $requestData = $this->trademeHelper->prepareData($product);
                            if (isset($imageid['success'])) {
                                $requestData['data']['PhotoIds'] = $imageid['success'];
                            }
                            if (isset($requestData['error'])) {
                                $this->trademeHelper->saveResponseOnProduct($requestData, $product);
                                foreach ($collection as $value) {
                                    $value->setThresholdLimit((int)$value->getThresholdLimit() + 1);
                                    $value->save();
                                }

                                $this->logger->addError('In UpdateInventory Cron: error',
                                    ['path' => __METHOD__, 'account_id' => $accountId, 'Response' => "Error While Preparing Data for SKU: " . $product->getSku() . " " . $requestData['error']]);
                            } else {

                                $response = $this->helperData->productSync($requestData['data']);
                                $data = $this->productchange->load($accountId->getId());
                                if (isset($response['ErrorDescription'])) {
                                    $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
                                    $product->setData($prodStatusAccAttr, 'ended');
                                    $product->getResource()->saveAttribute($product, $prodStatusAccAttr);
                                    foreach ($collection as $value) {
                                        $value->setThresholdLimit((int)$value->getThresholdLimit() + 1);
                                        $value->save();
                                    }

                                } else {
                                    $this->trademeHelper->saveResponseOnProduct($response, $product);
                                    foreach ($id as $accountsId => $deleteIds) {
                                        $data->deleteFromProductChange($deleteIds, $type, $accountId->getId());
                                    }
                                }

                            }
                        } else {
                            $this->logger->addInfo("In Cron Included Product(s) data not found.", ['path' => __METHOD__]);

                        }

                    } catch (\Exception $e) {
                        $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
                    }
                }

            }
        }
        $this->logger->addError('In UpdateInventory Cron: Disable', ['path' => __METHOD__]);

    }
}
