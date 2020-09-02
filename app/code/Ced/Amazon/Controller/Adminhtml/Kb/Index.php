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

namespace Ced\Amazon\Controller\Adminhtml\Kb;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
//        $objectManager= \Magento\Framework\App\ObjectManager::getInstance();
//        $invoiceCron=$objectManager->create('Ced\Integrator\Cron\Observer\LogDelete');
//        $invoiceCron->execute();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Amazon::amazon');
        $resultPage->getConfig()->getTitle()->prepend(__('Amazon Kb'));
        return $resultPage;
    }
}
