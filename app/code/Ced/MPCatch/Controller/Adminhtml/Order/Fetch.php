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

namespace Ced\MPCatch\Controller\Adminhtml\Order;

class Fetch extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_MPCatch::mpcatch_orders';
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public $resultRedirectFactory;
    /**
     * @var \Ced\MPCatch\Helper\Order
     */
    public $orderHelper;

    /**
     * Fetch constructor.
     *
     * @param \Magento\Backend\App\Action\Context                  $context
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Ced\MPCatch\Helper\Order                             $orderHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Ced\MPCatch\Helper\Order $orderHelper
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderHelper = $orderHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $status = $this->orderHelper->importOrders();
        if ($status === true) {
            $this->messageManager->addSuccessMessage(" New Catch Orders fetched successfully");
        } else {
            $this->messageManager->addErrorMessage("No New Catch Orders");
        }
        $result = $this->resultRedirectFactory->create();
        $result->setPath('mpcatch/order/index');
        return $result;
    }
}
