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

use Magento\Framework\View\Result\PageFactory;

/**
 * Class Fetch
 * @package Ced\GXpress\Controller\Adminhtml\Order
 */
class Fetch extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_GXpress::gxpress_orders';
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public $resultRedirectFactory;
    /**
     * @var \Ced\GXpress\Helper\Order
     */
    public $orderHelper;

    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Fetch constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Ced\GXpress\Helper\Logger $logger
     * @param \Ced\GXpress\Helper\Order $orderHelper
     *
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Ced\GXpress\Helper\Logger $logger,
        \Ced\GXpress\Helper\Order $orderHelper,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        PageFactory $resultPageFactory
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->logger = $logger;
        $this->orderHelper = $orderHelper;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $acccounts = $this->multiAccountHelper->getAllAccounts(true);
            $acccountIds = $acccounts->getColumnValues('id');

            if (!empty($acccountIds)) {
                $accountIds = (array_chunk($acccountIds, 1));
                $this->_session->setOrderAccountChunks($accountIds);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('Ced_GXpress::orders');
                $resultPage->getConfig()->getTitle()->prepend(__('Fetch Order From GXpress'));
                return $resultPage;
            } else {
                $this->messageManager->addErrorMessage(__('No Accounts available To fetch orders.'));
                return $this->_redirect('gxpress/order/index');
            }
        } catch (\Exception $e) {
            $this->logger->addError('In Fetch Order: '.$e->getMessage(), ['path' => __METHOD__]);
            return $this->_redirect('gxpress/order/index');
        }
    }
}
