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
class SyncProducts
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
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Ced\TradeMe\Helper\Order $orderHelper
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
     * @return boolean
     */
    public function execute()
    {
        try {
            $this->logger->addInfo('In SyncProducts');
            $order = true;
            $scopeConfigManager = $this->objectManager
                ->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $autoFetch = $scopeConfigManager->getValue('trademe_config/trademe_cron/sync_cron');
            $autoFetch = false;
            if ($autoFetch) {
                $schedulerCollection = $this->schedulerCollection->create()
                    ->addFieldToFilter('cron_type', 'sync')
                    ->addFieldToFilter('cron_status', 'to_sync');
                if ($schedulerCollection->getSize() > 0) {
                    foreach ($schedulerCollection as $schedulerColl) {
                        $statusArray = [];
                        $schedulerId = $schedulerColl->getId();
                        $schedulerData = $this->schedulerResource->create()->load($schedulerId);
                        if (count($schedulerData->getData()) > 0) {
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
                                $product = $this->objectManager->get('Magento\Catalog\Model\Product')->load($id);
                                //$imageid = $this->trademe->imageData($product, $acccountId);

                                $listingId = $this->multiAccountHelper->getProdListingIdAttrForAcc($acccountId);

                                $response = $this->dataHelper->getProductData($product->getData($listingId));
                                $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($acccountId);
                                if (isset($response['ListingId'])) {

                                    if (isset($response['CanRelist'])) {

                                        $product->setData($prodStatusAccAttr, 'ended');
                                        $product->getResource()->saveAttribute($product, $prodStatusAccAttr);
                                        $statusArray[$id] = '';
                                        $schedulerData->setCronStatus('synced');
                                        $this->logger->addInfo('In SyncProducts Cron: success',
                                            ['path' => __METHOD__, 'account_id' => $acccountId, 'Response' => "Successfully Synced Product for SKU: " . $product->getSku()]);


                                    } elseif(isset($response['RelistedItemId'])){
                                        $product->setData($prodStatusAccAttr, 'uploaded');
                                        $product->setData($listingId, $response['RelistedItemId']);
                                        $product->getResource()->saveAttribute($product, $prodStatusAccAttr)
                                            ->saveAttribute($product, $listingId);
                                        $success[] = $product->getSku();
                                    } else {

                                        $product->setData($prodStatusAccAttr, 'uploaded');
                                        $product->getResource()->saveAttribute($product, $prodStatusAccAttr);
                                        $statusArray[$id] = '';
                                        $schedulerData->setCronStatus('synced');
                                        $this->logger->addInfo('In SyncProducts Cron: success',
                                            ['path' => __METHOD__, 'account_id' => $acccountId, 'Response' => "Successfully Synced Product for SKU: " . $product->getSku()]);

                                        //$this->trademe->saveResponseOnProduct($response, $product);
                                    }
                                } elseif (isset($response['ErrorDescription'])) {

                                    $statusArray[$id] = json_encode($response);
                                    $this->logger->addError('In SyncProducts Cron: error',
                                        ['path' => __METHOD__, 'account_id' => $acccountId, 'Response' => "Error While Syncing Product for SKU: " . $product->getSku() . " " . json_encode($response)]);

                                } else {
                                    $product->setData($prodStatusAccAttr, 'not_uploaded');
                                    $product->getResource()->saveAttribute($product, $prodStatusAccAttr);
                                    $schedulerData->setCronStatus('synced');
                                    $statusArray[$id] = '';
                                    $this->logger->addInfo('In SyncProducts Cron: success',
                                        ['path' => __METHOD__, 'account_id' => $acccountId, 'Response' => "Successfully Synced Product for SKU: " . $product->getSku()]);

                                    $success[] = $product->getSku();
                                }


                            }
                            $schedulerData->setError(json_encode($statusArray));
                            $schedulerData->save();

                        }
                    }
                } else {
                    $this->scheduleSyncIds();
                }

            }
            $this->logger->addError('In SyncProducts Cron: Disable', ['path' => __METHOD__]);
            return true;
        } catch (\Exception $e) {
            $this->logger->addError('In SyncProducts Cron: Exception', ['path' => __METHOD__, 'Response' => $e->getMessage()]);
        }
        return true;
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
            $listingId = $this->multiAccountHelper->getProdListingIdAttrForAcc($account->getId());
            $activeProfileIds = $this->profileCollection
                ->addFieldToFilter('profile_status', 1)
                ->getColumnValues('id');
            $collection = $this->prodCollection->create()
                ->addAttributeToSelect($profileAccAttr);

            $prodIds = $collection
                ->setStoreId($storeId)
                ->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable')))
                ->addAttributeToFilter('visibility', 4)
                ->addAttributeToFilter($listingId, ['neq' => null])
                ->addAttributeToFilter($profileAccAttr, array('notnull' => true))
                ->addAttributeToFilter($profileAccAttr, array('in' => $activeProfileIds))
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
                $scheduler->setCronType('sync');
                $scheduler->save();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
