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

use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;

/**
 * Class Sync
 * @package Ced\GXpress\Controller\Adminhtml\Sync
 */
class Sync extends \Magento\Customer\Controller\Adminhtml\Group
{
    const ADMIN_RESOURCE = 'Ced_GXpress::GXpress';
    /**
     * @var
     */
    protected $_objectManager;
    /**
     * @var
     */
    protected $_session;

    /**
     * @var
     */
    public $multiAccountHelper;

    /**
     * @return $this|void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $id = $this->getRequest()->getParam('id');

        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if ($orderId && $accountId && $id) {
                if ($this->_coreRegistry->registry('gxpress_account'))
                    $this->_coreRegistry->unregister('gxpress_account');
                $this->multiAccountHelper = $this->_objectManager
                    ->create('\Ced\GXpress\Helper\MultiAccount')->getAccountRegistry($accountId);
                $model = $this->_objectManager->create('Ced\GXpress\Model\Orders')->load($id);
                if (!empty($model->getStatus()) && $model->getStatus() == 'failed') {
                    $GXpressLib = $this->_objectManager->create('Ced\GXpress\Helper\Order')->getNewOrders($accountId,$orderId);
                    if(isset($GXpressLib['error'])) {
                        $this->messageManager->addErrorMessage("You try to sync Failed order " . $orderId);
                        $this->_redirect('gxpress/order/index');
                        return;
                    }
                    $this->messageManager->addSuccessMessage(__('Order ' . $orderId . ' Sync Successfully'));
                } else {
                    $GXpressLib = $this->_objectManager->create('Ced\GXpress\Helper\GXpresslib')->fetchOrderFromGoogleExpressByOrderId($orderId);
                    $model->addData(['shipment_data' => json_encode($GXpressLib->getShipments())]);
                    $model->addData(['status' => $GXpressLib->getStatus()]);
                    $model->save();
                    $this->messageManager->addSuccessMessage(__('Order ' . $orderId . ' Sync Successfully'));
                    return $resultRedirect->setPath('gxpress/order/index');
                }
            }

        } catch (\Exception $e) {
            $this->_objectManager->create('Ced\GXpress\Helper\Logger')->addError('In Delete Order Row: ' . $e->getMessage(), ['path' => __METHOD__]);
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('gxpress/order/index');
        }
        $this->_redirect('gxpress/order/index');
        return;
    }
}

