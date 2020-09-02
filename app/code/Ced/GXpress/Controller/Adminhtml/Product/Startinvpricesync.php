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
 * @package   Ced_GXpress
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Ced\GXpress\Helper\GXpress;
use Ced\GXpress\Helper\Data;
use Ced\GXpress\Helper\Logger;

/**
 * Class Startinvpricesync
 * @package Ced\GXpress\Controller\Adminhtml\Product
 */
class Startinvpricesync extends Action
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
     * @var GXpress
     */
    public $gxpressHelper;
    /**
     * @var Data
     */
    public $dataHelper;
    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /** @var \Ced\GXpress\Helper\GXpresslib $gxpressLibHelper */
    public $gxpressLibHelper;

    /**
     * Startinvpricesync constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param Data $dataHelper
     * @param Logger $logger
     * @param GXpress $gxpressHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Ced\GXpress\Helper\MultiAccount $multiAccountHelper
     * @param \Ced\GXpress\Helper\GXpresslib $gxpressLibHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        Data $dataHelper,
        Logger $logger,
        GXpress $gxpressHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        \Ced\GXpress\Helper\GXpresslib $gxpressLibHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->gxpressHelper = $gxpressHelper;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->gxpressLibHelper = $gxpressLibHelper;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $message = [];
        $message['error'] = "";
        $message['success'] = "";
        $finalXml = '';
        $error = $successids ='';

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
                    if ($this->_coreRegistry->registry('gxpress_account'))
                        $this->_coreRegistry->unregister('gxpress_account');
                    $this->multiAccountHelper->getAccountRegistry($accountId);
                    $this->dataHelper->updateAccountVariable();
                    $this->gxpressHelper->updateAccountVariable();
                    $checkError = false;
                    foreach ($prodIds as $id) {
                        $finaldata = $this->gxpressLibHelper->updatePriceInventory($id);
                        if (isset($finaldata['type']) && $finaldata['type'] == 'success') {
                            $checkError = true;
                            $finalXml .= $finaldata['data'];
                            $message['success'] = $finalXml;
                        } else {
                            $error .= $finaldata['data'];
                        }
                    }
                    if ($error) {
                        $message['error'] = $error;
                    }
                }
                /*if (!empty($message['success'])) {
                    $message['success'] = "Batch ". $index .": " .$message['success'];
                }
                if (!empty($message['error'])) {
                    $message['error'] = "Batch ".$index. ": " .$message['error'];
                }*/
            } else {
                $message['error'] = $message['error']." included Product(s) data not found.";
            }
        } catch (\Exception $e) {
            $message['error'] = $e->getMessage();
            $this->logger->addError($message['error'], ['path' => __METHOD__]);
        }
        return $resultJson->setData($message);
    }

    /**
     * @param $invPriceSyncOnGXpress
     * @return string
     */
    public function getErrors($invPriceSyncOnGXpress)
    {
        $message = [];
        if (!isset($invPriceSyncOnGXpress->LongMessage)) {
            foreach ($invPriceSyncOnGXpress as $errorMessage) {
                $message[] =  $errorMessage->LongMessage;
            }
        } else {
            $message[] = $invPriceSyncOnGXpress->LongMessage;
        }
        return implode(', ', $message);
    }
}
