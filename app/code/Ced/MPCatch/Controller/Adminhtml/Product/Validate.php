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
 * @package   Ced_MPCatch
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MPCatch\Controller\Adminhtml\Product;

class Validate extends \Magento\Framework\App\Action\Action
{

    const CHUNK_SIZE = 10;

    /**
     * @var \Magento\Backend\Model\Session
     */
    public $session;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * @var \Ced\MPCatch\Helper\Product
     */
    public $mpcatch;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    public $catalogCollection;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Validate constructor.
     *
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Catalog\Model\Product                   $collection
     * @param \Ced\MPCatch\Helper\Product                      $product
     * @param \Ced\MPCatch\Helper\Config                       $config
     * @param \Magento\Framework\View\Result\PageFactory       $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter          $filter
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(        
        \Ced\MPCatch\Helper\Config $config,
        \Ced\MPCatch\Helper\Product $product,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product $collection,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {

        parent::__construct($context);
        $this->filter = $filter;
        $this->catalogCollection = $collection;
        $this->mpcatch = $product;
        $this->session = $context->getSession();
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $batchId = $this->getRequest()->getParam('batchid');
        if (isset($batchId)) {
            $resultJson = $this->resultJsonFactory->create();
            $productIds = $this->session->getMPCatchProducts();
            $response = $this->mpcatch->validateAllProducts($productIds[$batchId]);
            if (isset($productIds[$batchId]) && $response) {
                return $resultJson->setData(
                    [
                    'success' => count($productIds[$batchId]) . " Product(s) Validation Process Executed successfully.",
                    'messages' => $response
                    ]
                );
            }
            return $resultJson->setData(
                [
                'error' => count($productIds[$batchId]) . " Product(s) Validation Process Execution Failed.",
                'messages' => $this->registry->registry('mpcatch_product_errors'),
                ]
            );
        }

        // case 3 normal uploading and chunk creating
        $collection = $this->filter->getCollection($this->catalogCollection->getCollection());
        $productIds = $collection->getAllIds();

        if (count($productIds) == 0) {
            $this->messageManager->addErrorMessage('No Product selected to validate.');
            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        // case 3.1 normal uploading if current ids are equal to chunk size.
        if (count($productIds) == self::CHUNK_SIZE) {
            $response = $this->mpcatch->validateAllProducts($productIds);
            if ($response) {
                $this->messageManager->addSuccessMessage(count($productIds) . ' Product(s) Validation Process Executed successfully.');
            } else {
                $message = 'Product(s) Validate Failed.';
                $errors = $this->registry->registry('mpcatch_product_errors');
                if (isset($errors)) {
                    $message = "Product(s) Validate Failed. \nErrors: " . (string)json_encode($errors);
                }
                $this->messageManager->addError($message);
            }

            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $productIds = array_chunk($productIds, self::CHUNK_SIZE);
        $this->registry->register('productids', count($productIds));
        $this->session->setMPCatchProducts($productIds);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_MPCatch::MPCatch');
        $resultPage->getConfig()->getTitle()->prepend(__('Product Validate'));
        return $resultPage;
    }
}
