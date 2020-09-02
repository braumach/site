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
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Massinvpricesync
 * @package Ced\GXpress\Controller\Adminhtml\Product
 */
class Massinvpricesync extends Action
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

    /**
     * Massinvpricesync constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
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

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
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
                $this->_session->setUploadChunks($productIdsToSync);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('Ced_GXpress::product');
                $resultPage->getConfig()->getTitle()->prepend(__('Sync Price Inventory On GXpress'));
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
