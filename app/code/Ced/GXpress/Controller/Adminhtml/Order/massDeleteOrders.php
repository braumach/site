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

namespace Ced\GXpress\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class massDeleteOrders extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    public $helper;


    public $filter;

    public $gxpressOrdersCollectionFactory;


    public $gxpressOrdersFactory;

    /**
     * FailedOrders constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Ced\GXpress\Helper\Orders $helper
     */
    public function __construct(
        \Ced\GXpress\Model\ResourceModel\Orders\CollectionFactory $gxpressOrdersCollectionFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\GXpress\Model\OrdersFactory $gxpressOrdersFactory,
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        $this->filter = $filter;
        $this->gxpressOrdersCollectionFactory = $gxpressOrdersCollectionFactory;
        $this->gxpressOrdersFactory = $gxpressOrdersFactory;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $dataPost = $this->getRequest()->getParam('filters');
        if($dataPost) {
            $gxpressOrdersModelIds = $this->filter->getCollection($this->gxpressOrdersCollectionFactory->create())->getAllIds();
        } else {
            $gxpressOrdersModelIds[] = $this->getRequest()->getParam('id');
        }
        
        if(isset($gxpressOrdersModelIds)) {
            try {
                foreach ($gxpressOrdersModelIds as $gxpressOrdersModelId) {
                    $this->gxpressOrdersFactory->create()
                        ->load($gxpressOrdersModelId)
                        ->delete();
                }
                $count = count($gxpressOrdersModelIds);
                if($count) {
                    $this->messageManager->addSuccess(
                        __($count .' Order(s) Delete Succesfully')
                    );
                }
                else {
                    $this->messageManager->addErrorMessage(__(' Order Not Deleted '));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__(''.$e->getMessage()));
            }
        }
        else {
            $this->messageManager->addErrorMessage(__('Please Select Order '));
        }
        return $this->_redirect('*/*/index');
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_GXpress::GXpress');
    }
}