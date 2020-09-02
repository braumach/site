<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Order\Invoice;

use Ced\Amazon\Helper\Order\Invoice;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Upload extends Action
{
    public $resultPageFactory;
    public $invoice;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Invoice $invoice
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->invoice = $invoice;
    }

    public function execute()
    {
        $magentoOrderId=$this->getRequest()->getParam('magento_order_id');
        if (isset($magentoOrderId)) {
            $response=$this->invoice->upload([$magentoOrderId]);
            if ($response ==1) {
                $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
                $response->setData('uploaded');
                return $response;
            } else {
                $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
                $response->setData('not uploaded');
                return $response;
            }
        }
    }
}
