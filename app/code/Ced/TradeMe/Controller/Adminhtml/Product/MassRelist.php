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
 * Class MassRelist
 * @package Ced\TradeMe\Controller\Adminhtml\Product
 */
class MassRelist extends Action
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

    const ADMIN_RESOURCE = 'Ced_TradeMe::TradeMe';

    /**
     * @var \Ced\TradeMe\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * Massrelist constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $collectionFactory,
        Filter $filter,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->catalogCollection = $collectionFactory;
        $this->filter = $filter;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $productIdsToReList = [];
        $accountId = $this->_session->getAccountId();
        $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
        $ids = $this->filter->getCollection($this->catalogCollection->create()->addFieldToFilter($prodStatusAccAttr, 'ended'))->getAllIds();
        if (!empty($ids)) {
            $scopeConfigManager = $this->objectManager
                ->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $chunksize = $scopeConfigManager->getValue('trademe_config/product_upload/chunk_size');

            if ($chunksize == null)
                $chunksize = 50;

            $productids = array_chunk($ids, $chunksize);
            foreach ($productids as $prodChunkKey => $prodids) {
                $productIdsToReList[$prodChunkKey] = array($accountId => $prodids);
            }
            $this->_session->setUploadChunks($productIdsToReList);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Ced_TradeMe::product');
            $resultPage->getConfig()->getTitle()->prepend(__('Relist Product(s) On TradeMe'));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('No product available for relisting.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
