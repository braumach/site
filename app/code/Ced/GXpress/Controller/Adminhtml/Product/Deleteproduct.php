<?php
namespace Ced\GXpress\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class Deleteproduct extends Action
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

    const ADMIN_RESOURCE = 'Ced_GXpress::GXpress';

    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $collectionFactory,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        Filter $filter
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->catalogCollection = $collectionFactory;
        $this->filter = $filter;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    public function execute()
    {
        try{
            $productIdsToSync = [];
            $accountId = $this->_session->getAccountId();
            $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
            $ids = $this->getRequest()->getParam('selected');
            //$ids = $this->filter->getCollection($this->catalogCollection->create()->addFieldToFilter($prodStatusAccAttr, 4))->getAllIds();
            if (!empty($ids)) {
                $productids = array_chunk($ids, 4);
                foreach ($productids as $prodChunkKey => $prodids) {
                    $productIdsToSync[$prodChunkKey] = array($accountId => $prodids);
                }
                $this->_session->setDeleteChunks($productIdsToSync);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('Ced_GXpress::product');
                $resultPage->getConfig()->getTitle()->prepend(__('Delete Product On GXpress'));
                return $resultPage;
            } else {
                $this->messageManager->addErrorMessage(__('No product available for Inventory sync.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('No product Assign on Profile'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}