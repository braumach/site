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
 * @package     Ced_GXpress
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Controller\Adminhtml\Profile;

use Magento\Framework\View\Result\PageFactory;

/**
 * Class EditProfileProductGrid
 * @package Ced\GXpress\Controller\Adminhtml\Profile
 */
class EditProfileProductGrid extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    const ADMIN_RESOURCE = 'Ced_GXpress::GXpress';

    /**
     * EditProfileProductGrid constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->multiAccountHelper = $multiAccountHelper;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $profileId = $this->getRequest()->getParam('id');
        $this->multiAccountHelper->getAccountRegistryByPId($profileId);
        return $this->resultPageFactory->create();
    }
}
