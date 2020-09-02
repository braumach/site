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
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Strategy;

/**
 * Class Edit
 *
 * @package Ced\Amazon\Controller\Adminhtml\Strategy
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $title = 'Add Strategy';
        if (isset($id) && !empty($id)) {
            $title = 'Edit Strategy';
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Amazon::strategies');
        $resultPage->getConfig()->getTitle()->prepend(__($title));
        return $resultPage;
    }
}
