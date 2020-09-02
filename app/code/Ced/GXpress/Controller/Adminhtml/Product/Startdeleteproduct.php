<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 4/3/19
 * Time: 6:37 PM
 */

namespace Ced\GXpress\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Ced\GXpress\Helper\GXpress;
use Ced\GXpress\Helper\Data;
use Ced\GXpress\Helper\Logger;

class Startdeleteproduct extends Action
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

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        Data $dataHelper,
        Logger $logger,
        GXpress $gxpressHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        \Ced\GXpress\Helper\GXpresslib $gxpressLibHelper)
    {
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
        $error = $successids ='';
        $finalXml = '';
        $key = $this->getRequest()->getParam('index');
        $totalChunk = $this->_session->getDeleteChunks();
        $index = $key + 1;
        if (count($totalChunk) <= $index) {
            $this->_session->unsDeleteChunks();
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
                        $finaldata = $this->gxpressLibHelper->deleteRequest($id);
                        if (isset($finaldata['type']) && $finaldata['type'] == 'success') {
                            $checkError = true;
                            $finalXml .= $finaldata['data'];
                        } else {
                            $error .= $finaldata['data'];
                        }
                    }
                    if ($error) {
                        $message['error'] = $error;
                    }
                }
            } else {
                $message['error'] = $message['error']." included Product(s) data not found.";
            }
        } catch (\Exception $e) {
            $message['error'] = $e->getMessage();
            $this->logger->addError($message['error'], ['path' => __METHOD__]);
        }
        return $resultJson->setData($message);
    }
}