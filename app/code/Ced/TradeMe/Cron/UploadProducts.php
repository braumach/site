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
use Magento\Backend\Model\Session;


/**
 * Class SyncProducts
 * @package Ced\TradeMe\Cron
 */
class UploadProducts
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
    public $schedulerResource;
    public $schedulerCollection;
    public $profileCollection;
    public $prodCollection;
    public $adminSession;

    /**
     * AutoRelist constructor.
     * @param Data $dataHelper
     * @param \Ced\TradeMe\Helper\Logger $logger
     * @param \Ced\TradeMe\Helper\Order $orderHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Ced\TradeMe\Helper\TradeMe $trademe
     * @param \Ced\TradeMe\Model\ResourceModel\Profile\Collection $profileResource
     * @param \Ced\TradeMe\Model\ResourceModel\JobScheduler\CollectionFactory $schedulerCollection
     * @param \Ced\TradeMe\Model\JobSchedulerFactory $schedulerResource
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $prodCollection
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper
     * @param Session $session
     */
    public function __construct(
        Data $dataHelper,
        \Ced\TradeMe\Helper\Logger $logger,
        \Ced\TradeMe\Helper\Order $orderHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\TradeMe\Helper\TradeMe $trademe,
        \Ced\TradeMe\Model\ResourceModel\Profile\Collection $profileResource,
        \Ced\TradeMe\Model\ResourceModel\JobScheduler\CollectionFactory $schedulerCollection,
        \Ced\TradeMe\Model\JobSchedulerFactory $schedulerResource,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $prodCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper,
        Session $session
    )
    {
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
        $this->orderHelper = $orderHelper;
        $this->profileCollection = $profileResource;
        $this->schedulerCollection = $schedulerCollection;
        $this->schedulerResource = $schedulerResource;
        $this->trademe = $trademe;
        $this->adminSession = $session;
        $this->_coreRegistry = $coreRegistry;
        $this->prodCollection = $prodCollection;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Ced\TradeMe\Helper\Order
     */
    public function execute()
    {
        try {
            $this->logger->addInfo('In UploadProducts');
            $order = true;
            $synced = false;
            $scopeConfigManager = $this->objectManager
                ->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $autoFetch = $scopeConfigManager->getValue('trademe_config/trademe_cron/upload_cron');
            //$autoFetch = true;
            if ($autoFetch) {
                $schedulerCollection = $this->schedulerCollection->create()
                    ->addFieldToFilter('cron_type', 'upload')
                    ->addFieldToFilter('cron_status', 'to_sync');
                if ($schedulerCollection->getSize() > 0) {
                    foreach ($schedulerCollection as $schedulerColl) {
                        $statusArray = [];
                        $schedulerId = $schedulerColl->getId();

                        $schedulerData = $this->schedulerResource->create()->load($schedulerId);
                        if (count($schedulerData->getData()) > 0) {
                            if ($schedulerData->getThreshold() == null || $schedulerData->getThreshold() <= 2) {
                            $productIds = $schedulerData->getProductIds();
                            $productIds = is_string($productIds) ? /*explode*/
                                (/*",",*/
                                json_decode($productIds, true)) : array();
                            $acccountId = $schedulerData->getAccountId();


                            if ($this->_coreRegistry->registry('trademe_account'))
                                $this->_coreRegistry->unregister('trademe_account');
                            $this->multiAccountHelper->getAccountRegistry($acccountId);
                            $this->dataHelper->updateAccountVariable();
                            foreach ($productIds as $key => $id) {
                                $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id);
                                $listingIdAttr = $this->multiAccountHelper->getProdListingIdAttrForAcc($acccountId);
                                $prodError = $this->multiAccountHelper->getProdListingErrorAttrForAcc($acccountId);
                                $listingId = $product->getData($listingIdAttr);
                                if (empty($listingId)) {
                                    $synced = true;
                                    /* print_r($product->getEntityId());
                                     die('ggg');*/
                                    $imageid = '';
                                    if ($product->getTypeId() == 'simple') {

                                        $imageid = $this->trademe->imageData($product, $acccountId);
                                        if (isset($imageid['error'])) {
                                            $error[] = "Error While Uploading Images for SKU: " . $product->getSku() . " " . $imageid['error'];
                                        }
                                    }

                                    $requestData = $this->trademe->prepareData($product);

                                    unset($requestData['data']['ListingId']);

                                    if (isset($imageid['success']) && !empty($imageid['success'][0])) {
                                        $requestData['data']['PhotoIds'] = $imageid['success'];
                                    }
                                    if (isset($requestData['error'])) {
                                        if ($schedulerColl->getThreshold() == null)
                                            $schedulerData->setThreshold(1);
                                        else
                                            $schedulerData->setThreshold($schedulerColl->getThreshold() + 1);
                                        $this->trademe->saveResponseOnProduct($requestData, $product);
                                        $this->logger->addError('In UploadProduct Cron: error',
                                            ['path' => __METHOD__, 'account_id' => $acccountId, 'Response' => "Error While Uploading Product for SKU: " . $product->getSku() . " " . json_encode($requestData['error'])]);

                                    } else {

                                        $response = $this->dataHelper->productUpload($requestData['data']);

                                        $this->trademe->saveResponseOnProduct($response, $product);

                                        if (isset($response['Success']) && isset($response['ListingId'])) {
                                            $statusArray[$id] = '';
                                            $schedulerData->setCronStatus('synced');
                                            $this->logger->addInfo('In UploadProduct Cron: success',
                                                ['path' => __METHOD__, 'account_id' => $acccountId, 'Response' => "Successfully Uploaded Product for SKU: " . $product->getSku()]);

                                        } else {
                                            $statusArray[$id] = json_encode($response);
                                            $this->logger->addError('In UploadProduct Cron: error',
                                                ['path' => __METHOD__, 'account_id' => $acccountId, 'Response' => "Error While Uploading Product for SKU: " . $product->getSku() . " " . json_encode($response)]);
                                            $product->setData($prodError, json_encode($response));
                                            $product->getResource()->saveAttribute($product, $prodError);
                                            if ($schedulerColl->getThreshold() == null)
                                                $schedulerData->setThreshold(1);
                                            else
                                                $schedulerData->setThreshold($schedulerColl->getThreshold() + 1);
                                        }
                                    }

                                }

                            }
                            if (!$synced) {
                                $schedulerData->setCronStatus('synced');
                            }
                            $schedulerData->setError(json_encode($statusArray));
                            $schedulerData->save();

                        } else {
                                $schedulerData->setCronStatus('synced');
                                $schedulerData->save();

                            }
                    }

                    }
                } else {
                    $this->scheduleSyncIds();
                }

            }
            $this->logger->addError('In Product Upload Cron: Disable', ['path' => __METHOD__]);
            return $order;
        } catch (\Exception $e) {
            $this->logger->addError('In Product Upload Cron: Exception', ['path' => __METHOD__, 'Response' => $e->getMessage()]);
        }
    }

    public function scheduleSyncIds()
    {
        $hasError = false;
        $prodCollection = $this->getAllAssignedProductCollectionToInvSync();
        if (count($prodCollection) > 0) {
            $accountIndexes = $this->getAccountIndexesSession();

            foreach ($prodCollection as $chunkIndex => $collectionIds) {
                $accountIdToSchedule = '';
                foreach ($accountIndexes as $accountId => $accountIndex) {
                    if ($chunkIndex <= $accountIndex['end_index'] && $chunkIndex >= $accountIndex['start_index']) {
                        $accountIdToSchedule = $accountId;
                    }
                }
                $scheduled = $this->createSchedulerForIdsWithActionToSync($collectionIds, $accountIdToSchedule);
                if (!$scheduled && !$hasError) {
                    $hasError = true;
                }
            }


            if (!$hasError) {
                $this->logger->addInfo('Schedule Sync Ids', array('path' => __METHOD__, 'Response' => 'Product Ids Scheduled for Status Sync.'));
            } else {
                $this->logger->addInfo('Schedule Sync Ids', array('path' => __METHOD__, 'Response' => 'Something Went Wrong while scheduling Product Ids Scheduled for Status Sync'));
            }
        } else {
            $this->logger->addInfo('Schedule Sync Ids', array('path' => __METHOD__, 'Response' => 'No Product Assigned in Active Profiles.'));
        }
        return $hasError;
    }

    public function getAllAssignedProductCollectionToInvSync()
    {
        $productIdsToSchedule = $accountChunks = array();
        $accounts = $this->multiAccountHelper->getAllAccounts(true);
        foreach ($accounts as $account) {
            $arrKeys = [];
            $accountId = $account->getId();
            $storeId = $account->getAccountStore();
            $profileAccAttr = $this->multiAccountHelper->getProfileAttrForAcc($account->getId());
            $prodStatus = $this->multiAccountHelper->getProdListingIdAttrForAcc($accountId);
            $activeProfileIds = $this->profileCollection
                ->addFieldToFilter('profile_status', 1)
                ->getColumnValues('id');
            $collection = $this->prodCollection->create()
                ->addAttributeToFilter($prodStatus, ['null' => true])
                ->addAttributeToSelect($profileAccAttr);

            $prodIds = $collection
                ->setStoreId($storeId)
                ->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable')))
                ->addAttributeToFilter('visibility', 4)
                ->addAttributeToFilter($profileAccAttr, array('notnull' => true))
                ->addAttributeToFilter($prodStatus, array('null' => true))
                ->addAttributeToFilter($profileAccAttr, array('in' => $activeProfileIds/*'eq' => 4*/))
                ->getColumnValues('entity_id');


            $prodIdsChunks = array_chunk($prodIds, 10);
            $productIdsToSchedule = array_merge($productIdsToSchedule, $prodIdsChunks);
            $accountChunks[$accountId]['start_index'] = count($productIdsToSchedule) - count($prodIdsChunks);
            $arrKeys = array_keys($productIdsToSchedule);
            $accountChunks[$accountId]['end_index'] = end($arrKeys);
        }
        $this->adminSession->setAccountIndexes($accountChunks);
        return $productIdsToSchedule;
    }

    public function getAccountIndexesSession()
    {
        return $this->adminSession->getAccountIndexes();
    }

    public function createSchedulerForIdsWithActionToSync($collectionIds = array(), $accountId = null)
    {
        try {
            $prodIds = array_chunk($collectionIds, 4);
            foreach ($prodIds as $ids) {
                $idstring = json_encode(/*',',*/ $ids);
                /** @var \Ced\TradeMe\Model\JobScheduler $scheduler */
                $scheduler = $this->schedulerResource->create();
                $scheduler->setProductIds(/*json_encode*/ ($idstring));
                $scheduler->setCronStatus('to_sync');
                $scheduler->setAccountId($accountId);
                $scheduler->setCronType('upload');
                $scheduler->save();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
