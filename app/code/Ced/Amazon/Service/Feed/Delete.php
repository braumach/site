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

namespace Ced\Amazon\Service\Feed;

use Ced\Amazon\Api\Processor\BulkActionProcessorInterface;
use Ced\Amazon\Model\ResourceModel\Feed\CollectionFactory;
use Ced\Amazon\Repository\Feed as FeedRepository;
use Ced\Amazon\Helper\Logger;

class Delete implements BulkActionProcessorInterface
{
    /**
     * @var \Ced\Amazon\Model\ResourceModel\Feed\CollectionFactory
     */
    public $feedCollectionFactory;

    /**
     * @var \Ced\Amazon\Helper\Logger
     */
    public $logger;

    /** @var \Ced\Amazon\Repository\Feed */
    public $feedRepository;

    public function __construct(
        CollectionFactory $feedCollectionFactory,
        FeedRepository $feedRepository,
        Logger $logger
    ) {
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->feedRepository = $feedRepository;
        $this->logger = $logger;
    }

    public function process($ids)
    {
        try {
            $status = false;
            if (!empty($ids)) {
                /** @var \Ced\Amazon\Model\ResourceModel\Feed\Collection $collection */
                $collection = $this->feedCollectionFactory->create()->addFieldToFilter('id', ['in' => $ids]);
                if (isset($collection) && $collection->getSize() > 0) {
                    $this->feedRepository->clearRecords(null, $collection);
                    $status = true;
                }
            }
        } catch (\Exception $e) {
            $status = false;
            $this->logger->error(
                'Error in bulk feed delete',
                [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );
        }

        return $status;
    }
}
