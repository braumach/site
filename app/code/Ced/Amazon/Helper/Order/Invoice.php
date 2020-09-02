<?php
namespace Ced\Amazon\Helper\Order;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Psr\Log\LoggerInterface;

class Invoice
{
    public $invoiceCollection;
    public $invoice;
    public $fileFactory;
    public $dateTime;
    public $logger;
    public $feed;
    public $result;
    public $account;
    public $invoiceRepository;
    public $amazonOrderCollection;
    public $searchCriteriaBuilder;
    public $loggers;
    public $config;
    public $file;
    public $resource;
    public $modelFactory;
    public $serializer;
    public $directory;
    public $url;
    public $amazonOrderResourceModel;
    public $amazonOrderModel;

    public function __construct(
        InvoiceCollectionFactory $invoiceCollection,
        \Magento\Sales\Model\Order\Pdf\Invoice $invoice,
        FileFactory $fileFactory,
        DateTime $dateTime,
        \Ced\Amazon\Helper\Logger $logger,
        \Amazon\Sdk\Api\FeedFactory $feed,
        \Amazon\Sdk\Api\Feed\ResultFactory $result,
        \Ced\Amazon\Api\AccountRepositoryInterface $account,
        InvoiceRepositoryInterface $invoiceRepository,
        \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory $amazonOrderCollection,
        \Ced\Amazon\Model\OrderFactory $amazonOrderModel,
        \Ced\Amazon\Model\ResourceModel\Order $amazonOrderResourceModel,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $loggers,
        \Ced\Amazon\Helper\Config $config,
        \Magento\Framework\Filesystem\Io\File $file,
        \Ced\Amazon\Model\ResourceModel\Feed $resource,
        \Ced\Amazon\Model\FeedFactory $modelFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Framework\Filesystem\DirectoryList $directory
    ) {
        $this->invoiceCollection = $invoiceCollection;
        $this->invoice = $invoice;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->feed = $feed;
        $this->result = $result;
        $this->account = $account;
        $this->invoiceRepository = $invoiceRepository;
        $this->amazonOrderCollection = $amazonOrderCollection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->loggers = $loggers;
        $this->config = $config;
        $this->file = $file;
        $this->resource = $resource;
        $this->modelFactory = $modelFactory;
        $this->serializer = $serializer;
        $this->url = $url;
        $this->directory = $directory;
        $this->amazonOrderModel = $amazonOrderModel;
        $this->amazonOrderResourceModel = $amazonOrderResourceModel;
    }

    /**
     * @param array $orderIds
     * @return bool
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Zend_Pdf_Exception
     */
    public function upload($orderIds=[])
    {
        if ($this->config->amazonInvoiceUpload() == true) {
            foreach ($orderIds as $orderId) {
                $invoices = $this->getInvoiceDataByOrderId($orderId);
                if (empty($invoices)) {
                    continue;
                }
                foreach ($invoices as $invoice) {
                    $magentoInvoiceIncrementId = $invoice->getIncrementId();
                    $magentoInvoiceId = $invoice->getEntityId();
                }
                $amazonOrderCollection = $this->amazonOrderCollection->create()->addFieldToFilter('magento_order_id', ['eq' => $orderId])->getFirstItem();
                $amazonOrderId = $amazonOrderCollection->getAmazonOrderId();
                $accountId = $amazonOrderCollection->getAccountId();
                $orderIncrementId = $amazonOrderCollection->getMagentoIncrementId();
                $marketplaceId = $amazonOrderCollection->getMarketplaceId();
                $feedContent = null;
                $customPdfPath = null;
                //Invoice PDF Get
                if ($this->config->invoiceUploadType() == "magento-default-invoice-upload" && !empty($this->config->customInvoicePath())) {
                    $collection = $this->invoiceCollection->create()->addFieldToFilter('entity_id', ['eq' => 1]);
                    $feedContent = $this->invoice->getPdf($collection)->render();
                }
                if ($this->config->invoiceUploadType() == "custom-invoice-upload" && !empty($this->config->customInvoicePath())) {
                    $customPdfPath = $this->config->customInvoicePath() . $amazonOrderId . '.pdf';
                    if (file_exists($customPdfPath)) {
                        $file = fopen($customPdfPath, 'r');
                        $feedContent = fread($file, filesize($customPdfPath));
                        fclose($file);
                    } else {
                        continue;
                    }
                }
                $specifics = [
                    'amazon_order_id' => $amazonOrderId,
                    'account_id' => $accountId,
                    'magento_order_id' => $orderId,
                    'magento_order_increment_id' => $orderIncrementId,
                    'marketplace_id' => $marketplaceId,
                    'feed_content' => $feedContent,
                    'magento_invoice_id' => $magentoInvoiceId,
                    'magento_invoice_increment_id' => $magentoInvoiceIncrementId,
                    'custom_pdf_path' => $customPdfPath,
                    'ids' => $orderId
                ];
                $response= $this->sendInvoice($specifics);
                if ($response['Id']!=null) {
                    /** @var \Ced\Amazon\Model\Order $amazonOrderModel */
                    $amazonOrderModel= $this->amazonOrderModel->create()->load($orderId, 'magento_order_id');
                    $amazonOrderModel->setData(\Ced\Amazon\Model\Order::COLUMN_INVOICE_UPLOAD_STATUS, 1);
                    $this->amazonOrderResourceModel->save($amazonOrderModel);
                    return true;
                }
            }
        }
        return false;
    }

    public function sendInvoice($specifics)
    {
        $account = $this->account->getById($specifics['account_id']);
        $documentType = 'Invoice';
        $response = false;
        $feedType = \Amazon\Sdk\Api\Feed::ORDER_INVOICE_UPLOAD;
        $feed_options = "metadata:orderid=" . $specifics['amazon_order_id'] . ";metadata:invoicenumber=" . $specifics['magento_invoice_increment_id'] . ";metadata:documenttype=" . $documentType;
        $config = $account->getConfig();
        $config->setMarketplaceId($specifics['marketplace_id']);
        /**
         * Sending Feed to Amazon
         * @var \Amazon\Sdk\Api\Feed
         */
        $amz = $this->feed->create(
            [
                'config' => $config,
                'logger' => $this->logger,
                'mockMode' => $account->getMockMode(),
            ]
        );
        $amz->setFeedType($feedType);
        $amz->setFeedContent($specifics['feed_content']);
        $amz->setFeedOptions($feed_options);
        $amz->submitFeed();
        $response = $amz->getResponse();
        if ($response) {
            $responsePath = $this->createFile('response', $feedType);
            $lastResponse = $amz->getLastResponse();
            // Writing response to file
            if (isset($lastResponse['body'])) {
                /** @var boolean $written */
                $written = $this->file->write($responsePath, $lastResponse['body'], 0777);
                if ($written == false) {
                    $this->logger->error(
                        "Feed response write to file failed.",
                        [
                            'specifics' => $specifics,
                            'response' => $response,
                            'path' => __METHOD__,
                        ]
                    );
                }
            }

            // Saving in Amazon Feeds in DB
            $specifics['feed_content']=null;
            /** @var \Ced\Amazon\Model\Feed $feed */
            $feed = $this->modelFactory->create();
            $feed->addData([
                \Ced\Amazon\Model\Feed::COLUMN_ACCOUNT_ID => $specifics['account_id'],
                \Ced\Amazon\Model\Feed::COLUMN_FEED_ID => $response['FeedSubmissionId'],
                \Ced\Amazon\Model\Feed::COLUMN_CREATED_DATE => $response['SubmittedDate'],
                \Ced\Amazon\Model\Feed::COLUMN_EXECUTED_DATE => $response['SubmittedDate'],
                \Ced\Amazon\Model\Feed::COLUMN_STATUS => $response['FeedProcessingStatus'],
                \Ced\Amazon\Model\Feed::COLUMN_TYPE => $feedType,
                \Ced\Amazon\Model\Feed::COLUMN_FEED_FILE => $specifics['custom_pdf_path'],
                \Ced\Amazon\Model\Feed::COLUMN_RESPONSE_FILE => $responsePath,
                \Ced\Amazon\Model\Feed::COLUMN_SPECIFICS => json_encode($specifics),
                \Ced\Amazon\Model\Feed::COLUMN_PRODUCT_IDS => isset($specifics['ids']) ?
                    $this->serializer->serialize($specifics['ids']) : '[]',
            ]);
            $this->resource->save($feed);

            $response['Id'] = $feed->getId();

            return $response;
        }
        $content = null;

        $this->logger->error(
            "Feed send failed. Type: {$feedType}.",
            [
                'specifics' => $specifics,
                'content' => $content,
                'response' => $response,
                'path' => __METHOD__,
            ]
        );
        return $response;
    }
    public function getInvoiceDataByOrderId(int $orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)->create();
        try {
            $invoices = $this->invoiceRepository->getList($searchCriteria);
            $invoiceRecords = $invoices->getItems();
        } catch (Exception $exception) {
            $this->loggers->critical($exception->getMessage());
            $invoiceRecords = null;
        }
        return $invoiceRecords;
    }
    private function createFile($type = 'feed', $name = '_POST_PRODUCT_DATA_', $code = 'var')
    {
        $timestamp = uniqid();
        $path = $this->directory->getPath($code) . DS . 'amazon' . DS . strtolower($type);
        // Check if directory exists
        if (!$this->file->fileExists($path)) {
            $this->file->mkdir($path, 0777, true);
        }

        // File path
        $filePath = $path . DS . strtolower($name) . '-' . $timestamp . '.xml';

        // Check if file exists
        if (!$this->file->fileExists($filePath)) {
            $this->file->write($filePath, '', 0777);
        }

        return $filePath;
    }
}

//        $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];
//        $feedContent =$pdf->render();
//        $pdfpath=$this->fileFactory->create(
//            sprintf('invoice%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
//            $fileContent,
//            DirectoryList::VAR_DIR,
//            'application/pdf'
//        );
//        $file = fopen($pdfpath, 'r');
//        $pdf_feed = fread($file, filesize($pdfpath));
