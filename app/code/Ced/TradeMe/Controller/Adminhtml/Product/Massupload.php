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

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;


/**
 * Class Massupload
 * @package Ced\TradeMe\Controller\Adminhtml\Product
 */
class Massupload extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var CollectionFactory
     */
    public $catalogCollection;
    /**
     * @var Filter
     */
    public $filter;

    /**
     * @var \Ced\TradeMe\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    protected $objectManager;

    const ADMIN_RESOURCE = 'Ced_TradeMe::TradeMe';

    /**
     * Massupload constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $collectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper,
        Filter $filter
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->objectManager = $objectManager;
        $this->catalogCollection = $collectionFactory;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->filter = $filter;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('selected');

        $productids = $cids = $sids = [];
        $accountId = $this->_session->getAccountId();
        $accStatusAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);

        $alluploaded = $this->filter->getCollection($this->catalogCollection->create()->addFieldToFilter($accStatusAttr, ['in' => ['uploaded'/*, 'ended'*/]]))->getAllIds();

        $ccollection = $this->filter->getCollection($this->catalogCollection->create())->getAllIds();

        $scopeConfigManager = $this->objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $chunkSize = $scopeConfigManager->getValue('trademe_config/product_upload/chunk_size');

        if ($chunkSize == null)
            $chunkSize = 50;

        $ids = array_chunk(array_diff($ccollection, $alluploaded), $chunkSize);
        foreach ($ids as $prodChunkKey => $prodids) {
            $productids[$prodChunkKey] = array($accountId => $prodids);
        }
        if (!empty($productids)) {
            $this->_session->setUploadChunks($productids);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Ced_TradeMe::product');
            $resultPage->getConfig()->getTitle()->prepend(__('Add Product(s) On TradeMe'));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('No product available for upload.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
