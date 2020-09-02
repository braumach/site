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
 * Class Startendlist
 * @package Ced\TradeMe\Controller\Adminhtml\Product
 */
class Startendlist extends Action
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
    protected $scopeConfig;

    /**
     * Startendlist constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Data $dataHelper
     * @param Logger $logger
     * @param TradeMe $trademe
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper
     * @param Productstatus $productstatus
     * @param \Ced\TradeMe\Model\AccountsFactory $accountsFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Data $dataHelper,
        Logger $logger,
        \Ced\TradeMe\Helper\TradeMe $trademe,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
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
        $this->scopeConfig = $scopeConfig;
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
        $success = $error = [];
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
                    $withdrawType = $this->scopeConfig->getValue('trademe_config/product_upload/withdraw_type');
                    $withdrawReason = $this->scopeConfig->getValue('trademe_config/product_upload/withdraw_reason');
                    $withdrawSaleprice = $this->scopeConfig->getValue('trademe_config/product_upload/withdraw_saleprice');
                    foreach ($prodIds as $id) {
                        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id);
                        $listingIdAttr = $this->multiAccountHelper->getProdListingIdAttrForAcc($accountId);
                        $listingId = $product->getData($listingIdAttr);
                        $requestData = array('ListingId' => $listingId, 'ReturnListingDetails' => false, 'Type' => $withdrawType);
                        if($withdrawType == '1' && $withdrawSaleprice != null) {
                            $requestData['SalePrice'] = $withdrawSaleprice;
                        } elseif($withdrawType == '2' && $withdrawReason != null) {
                            $requestData['Reason'] = $withdrawReason;
                        }

                        $response =$this->dataHelper->withdrawAuction($requestData);
                        $prodStatusAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);

                        if (isset($response['Success']) && $response['Success'] == 1 && isset($response['ListingId']))
                        {
                            $product->setData($prodStatusAttr, 'ended');
                            $product->getResource()->saveAttribute($product, $prodStatusAttr);
                            $message['success'] .= $product->getSku();
                            $success[] = $product->getSku();

                        } else {
                            $prodValidationAttr = $this->multiAccountHelper->getProdListingErrorAttrForAcc($accountId);
                            $product->setData($prodValidationAttr, json_encode($response));

                            $product->getResource()->saveAttribute($product, $prodValidationAttr)->saveAttribute($product, $prodStatusAttr);
                            $message['error'] .= "Error From TradeMe While Withdraw Product SKU: ".$product->getSku()." ".json_encode($response);
                            $error = $product->getSku();
                        }

                    }
                }
                if (!empty($message['success'])) {
                    $message['success'] = "Batch ".$index.": ".implode(', ', $success)." Withdrawn Successfully";
                }
                if (!empty($message['error'])) {
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
