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
 * @package     Ced_2.3
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Service\Profile\Product;

use Ced\Amazon\Api\Processor\BulkActionProcessorInterface;
use Ced\Amazon\Helper\Logger;
use Ced\Amazon\Helper\Product\Relationship;
use Ced\Amazon\Model\ResourceModel\Profile\Product\CollectionFactory as productCollectionFactory;
use Ced\Amazon\Model\Source\Profile\Type;
use Ced\Amazon\Repository\Profile;
use Ced\Amazon\Service\Config;

class Save implements BulkActionProcessorInterface
{
    /**
     * @var \Ced\Amazon\Helper\Logger
     */
    public $logger;
    public $session;
    public $product;
    public $queue;
    public $profile;
    public $queueDataFactory;
    public $productCollectionFactory;
    public $config;
    public $productRepository;
    public $productInterfaceFactory;
    /**
     * @var \Ced\Amazon\Helper\Product
     */
    private $uploadProduct;


    public function __construct(
        Logger $logger,
        \Magento\Backend\App\Action\Context $context,
        Profile $profile,
        \Ced\Amazon\Helper\Product $uploadProduct,
        \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory $queueDataFactory,
        \Ced\Amazon\Api\QueueRepositoryInterface $queue,
        \Ced\Amazon\Repository\Profile\Product $profileProductRepository,
        \Ced\Amazon\Repository\Product $productRepository,
        productCollectionFactory $productCollectionFactory,
        \Ced\Amazon\Api\Data\ProductInterfaceFactory $productInterfaceFactory,
        Config $config
    )
    {
        $this->product = $profileProductRepository;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->session = $context->getSession();
        $this->queueDataFactory = $queueDataFactory;
        $this->queue = $queue;
        $this->profile = $profile;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->config = $config;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->uploadProduct = $uploadProduct;
    }

    public function process($ids)
    {
        try {
            $status = false;
            $profileId = $this->session->getProfileId();
            $profile = $this->profile->getById($profileId);
            $marketplace = $profile->getMarketplace();
            $accountId = $profile->getAccountId();
            $ids = $this->validateProfileProducts($ids, $marketplace, $accountId);
            if (!empty($ids) && is_array($ids)) {
                $added = $this->product->addProductsIdsWithProfileId($ids, $profileId);

                //If Product AutoUpload Is Enable
                if ($this->config->autoUploadOnAdd()) {

                    //product upload
                    $this->uploadProduct->update($ids, $throttle = true,
                        $operationType = \Amazon\Sdk\Base::OPERATION_TYPE_UPDATE);

                }

                // Resetting cache for all profiles
                $profileIds = $this->product->getProfileIdsByProductIds($ids);

//                 Removing cache for products
                foreach ($profileIds as $storeId => $id) {
                    if ($id != $profileId) {
                        $this->product->cleanByProfileId($profileId);
                    }
                }
//                if ($added &&
//                    in_array($profile->getType(), [Type::TYPE_AUTO, Type::TYPE_SEARCH_PRODUCT_UPLOAD])) {
//                    // Adding in queue for search and upload
//                    $specifics[\Ced\Amazon\Model\Queue::COLUMN_ACCOUNT_ID] = $profile
//                        ->getData(\Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID);
//                    $specifics[\Ced\Amazon\Model\Queue::COLUMN_MARKETPLACE] = $profile
//                        ->getData(\Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE);
//                    $specifics[\Ced\Amazon\Model\Queue::COLUMN_TYPE] =
//                        \Ced\Amazon\Cron\Queue\Search\Processor::PRODUCT_SEARCH;
//                    $specifics['ids'] = $ids;
//                    $this->queue($specifics);
//                }
            }
            $status = true;
        } catch (\Exception $e) {
            $status = false;
            $this->logger->error(
                'Error in Profile Product Save',
                [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );
        }

        return $status;
    }

    /**
     * @param $ids
     * @param $marketplace
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateProfileProducts($ids, $marketplace, $accountId)
    {
        $collection = $this->productCollectionFactory->create();
        $profileTableName = $collection->getTable(\Ced\Amazon\Model\Profile::NAME);

        $profileMarketplace = $collection->addFieldToFilter('product_id', ['in' => $ids])
            ->addFieldToFilter(\Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE, ['eq' => $marketplace])
            ->addFieldToFilter(\Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID, ['eq' => $accountId])
            ->addFieldToSelect(\Ced\Amazon\Model\Profile\Product::COLUMN_PRODUCT_ID)
            ->join(
                $profileTableName,
                $profileTableName . "." . \Ced\Amazon\Model\Profile::COLUMN_ID . "= main_table."
                . \Ced\Amazon\Model\Profile\Product::COLUMN_PROFILE_ID,
                \Ced\Amazon\Model\Profile::COLUMN_ID
            );
        $duplicateValues = $profileMarketplace->getData();
        $productIds = array_unique(array_column($duplicateValues, 'product_id'));

        if ($this->config->removeProductOnConflict()) {
            $profileIds = array_unique(array_column($duplicateValues, 'id'));
            foreach ($profileIds as $profileId) {
                $this->product->deleteByProductIdsAndProfileId($productIds, $profileId);
            }

            return $ids;
        } else {
            return array_diff($ids, $productIds);
        }
    }

    /**
     * @param array $specifics
     * @return bool
     * @throws \Exception
     */
    public function queue(array $specifics = [])
    {
        /** @var \Ced\Amazon\Api\Data\Queue\DataInterface $queueData */
        $queueData = $this->queueDataFactory->create();
        $queueData->setIds($specifics['ids']);
        $queueData->setAccountId($specifics['account_id']);
        $queueData->setMarketplace($specifics['marketplace']);
        $queueData->setSpecifics($specifics);
        $queueData->setOperationType('Update');
        $queueData->setType($specifics['type']);
        $status = $this->queue->push($queueData);
        return $status;
    }
}
