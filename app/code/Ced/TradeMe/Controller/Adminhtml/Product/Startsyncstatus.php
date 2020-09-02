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

namespace Ced\TradeMe\Controller\Adminhtml\Product;

use Ced\TradeMe\Helper\TradeMe;
use Ced\TradeMe\Model\Source\Productstatus;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Ced\TradeMe\Helper\Data;
use Ced\TradeMe\Helper\Logger;

/**
 * Class Startupload
 * @package Ced\TradeMe\Controller\Adminhtml\Product
 */
class Startsyncstatus extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var Data
     */
    public $dataHelper;
    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var \Ced\TradeMe\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    public $accountsFactory;
    public $productstatus;

    /**
     * Startupload constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Data $dataHelper
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Data $dataHelper,
        Logger $logger,
        \Ced\TradeMe\Helper\TradeMe $trademe,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper,
        \Ced\TradeMe\Model\Source\Productstatus $productstatus,
        \Ced\TradeMe\Model\AccountsFactory $accountsFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->trademe = $trademe;
        $this->productstatus = $productstatus;
        $this->_coreRegistry = $coreRegistry;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->accountsFactory = $accountsFactory;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $message = [];
        $message['error'] = "";
        $message['success'] = "";
        $key = $this->getRequest()->getParam('index');
        $totalChunk = $this->_session->getUploadChunks();
        $index = $key + 1;
        if (count($totalChunk) <= $index) {
            $this->_session->unsUploadChunks();
        }
        try {
            if (isset($totalChunk[$key])) {
                $ids = $totalChunk[$key];
                foreach ($ids as $accountId => $prodIds) {
                    if (!is_array($prodIds)) {
                        $prodIds[] = $prodIds;
                    }
                    if ($this->_coreRegistry->registry('trademe_account'))
                        $this->_coreRegistry->unregister('trademe_account');
                    $this->multiAccountHelper->getAccountRegistry($accountId);
                    $this->dataHelper->updateAccountVariable();

                    foreach ($prodIds as $id) {
                        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id);
                        $listingId = $this->multiAccountHelper->getProdListingIdAttrForAcc($accountId);
                        $response = $this->dataHelper->getProductData($product->getData($listingId));

                        $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
                        if (isset($response['ListingId'])) {

                            if (isset($response['CanRelist'])) {

                                $product->setData($prodStatusAccAttr, 'ended');
                                $product->getResource()->saveAttribute($product, $prodStatusAccAttr);
                                $success[] = $product->getSku();

                            } elseif(isset($response['RelistedItemId'])){
                                $product->setData($prodStatusAccAttr, 'uploaded');
                                $product->setData($listingId, $response['RelistedItemId']);
                                $product->getResource()->saveAttribute($product, $prodStatusAccAttr)
                                    ->saveAttribute($product, $listingId);
                                $success[] = $product->getSku();
                            }
                            else {

                                $product->setData($prodStatusAccAttr, 'uploaded');
                                $product->getResource()->saveAttribute($product, $prodStatusAccAttr);
                                $success[] = $product->getSku();
                                //$this->trademe->saveResponseOnProduct($response, $product);
                            }
                       } elseif (isset($response['ErrorDescription'])) {
                            $trademeerror = isset($response['ErrorDescription']) ? $response['ErrorDescription'] : json_encode($response);

                            $error[] = "Error While Uploading Product SKU: ".$product->getSku()." ".$trademeerror;
                        } else {
                            $product->setData($prodStatusAccAttr, 'not_uploaded');
                            $product->getResource()->saveAttribute($product, $prodStatusAccAttr);
                            $success[] = $product->getSku();
                        }

                    }
                }
                if (!empty($success)) {
                    $message['success'] = "Batch ".$index.": ".implode(', ', $success)." Synced Successfully";
                }
                if (!empty($error)) {
                    $message['error'] = "Batch ".$index.": ".implode(', ', $error);
                }
            } else {
                $message['error'] = "Batch ".$index.": ".$message['error']." included Product(s) data not found.";
            }
        } catch (\Exception $e) {
            $message['error'] = $e->getMessage();
            $this->logger->addError($message['error'], ['path' => __METHOD__]);
        }
        return $resultJson->setData($message);
    }
}
