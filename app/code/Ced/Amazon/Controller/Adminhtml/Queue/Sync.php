<?php
namespace Ced\Amazon\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;

class Sync extends Action
{
    const ADMIN_RESOURCE = 'Ced_Amazon::queue';

    public $queueCollectionFactory;
    public $filter;
    public $config;
    public $logger;
    public $dateTime;
    public $reportProcessor;
    public $synchronize;
    public $queueProcessor;
    public $actionFilter;
    public $queue;
    public $search;

    public function __construct(
        Action\Context $context,
        \Ced\Amazon\Model\ResourceModel\Queue\CollectionFactory $collectionFactory,
        \Magento\Ui\Component\MassAction\Filter $actionFilter,
        \Ced\Amazon\Helper\Logger $logger,
        \Ced\Amazon\Cron\Queue\Report\Processor $reportProcessor,
        \Ced\Amazon\Cron\Queue\Sychronize $synchronize,
        \Ced\Amazon\Cron\Queue\Processor $queueProcessor,
        \Magento\Framework\Api\FilterFactory $filter,
        \Ced\Amazon\Api\QueueRepositoryInterface $queue,
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search
    ){
        parent::__construct($context);
        $this->queueCollectionFactory = $collectionFactory;
        $this->actionFilter = $actionFilter;
        $this->logger = $logger;
        $this->reportProcessor = $reportProcessor;
        $this->synchronize = $synchronize;
        $this->queueProcessor = $queueProcessor;
        $this->filter = $filter;
        $this->queue = $queue;
        $this->search = $search;
    }

    public function execute()
    {
        $isFilter = $this->getRequest()->getParam('filters');
        if (isset($isFilter)) {
            $collection = $this->actionFilter->getCollection($this->queueCollectionFactory->create());
        } else {
            $id = $this->getRequest()->getParam('id');
            if (isset($id) && !empty($id)) {
                $collection = $this->queueCollectionFactory->create()->addFieldToFilter('id', ['eq' => $id]);
            }
        }

        $response = false;
        $message = 'Queue(s) synced successfully.';
        if (isset($collection) && $collection->getSize() > 0) {

            /** @var \Ced\Amazon\Model\Queue $item */
            foreach ($collection as $item) {
                try {
                    $specifics = $item->getSpecifics();
                    $type = $specifics['type'];
                    if (in_array($type, \Ced\Amazon\Cron\Queue\Report\Processor::reportType)) {
                        $items = $this->getList($item);
                        $this->reportProcessor->process($items);
                        $response = true;
                    } else {
                        if (isset($specifics['feed_id']) && $specifics['feed_id'] != '0') {
                            $this->synchronize->update($item, $specifics);
                            $response = true;
                        } else {
                            $items = $this->getList($item);
                            $this->queueProcessor->type = $specifics['type'];
                            $this->queueProcessor->process($items);
                            $response = true;
                        }
                    }
                } catch (\Exception $e) {
                    $item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::ERROR);
                    $this->logger->error(
                        "Queue item processing failure. Exception: " . $e->getMessage(),
                        [
                            'path' => __METHOD__,
                            'type' => $type,
                            'item' => $item->getData()
                        ]
                    );
                    $response = false;
                }
            }
        }

        if ($response) {
            $this->messageManager->addSuccessMessage($message);
        } else {
            $this->messageManager->addErrorMessage('Queue(s) sync failed.');
        }

        return $this->_redirect('amazon/queue');
    }

    /**
     * @param $item
     * @return \Ced\Amazon\Api\Data\QueueInterface[]
     */
    public function getList($item)
    {
        $idFilter = $this->filter->create();
        $idFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_ID)
            ->setConditionType('eq')
            ->setValue($item->getId());
        $criteria = $this->search->create();
        $criteria->addFilter($idFilter);

        // Getting the queue records for current feed type.
        /** @var \Ced\Amazon\Api\Data\QueueSearchResultsInterface $list */
        $list = $this->queue->getList($criteria->create());
        /** @var \Ced\Amazon\Api\Data\QueueInterface[] $items */
        $items = $list->getItems();
        return $items;

    }
}
