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
 * @package     Ced_TradeMe
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\TradeMe\Controller\Adminhtml\Profile;

use Ced\TradeMe\Helper\Data;

/**
 * Class UpdateCategoryAttributes
 * @package Ced\TradeMe\Controller\Adminhtml\Profile
 */
class UpdateCategoryAttributes extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_TradeMe::TradeMe';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\TradeMe\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var Data
     */
    public $helper;

    /**
     * UpdateCategoryAttributes constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper,
        Data $helper
    )
    {
        parent::__construct($context);
        $this->multiAccountHelper = $multiAccountHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->helper = $helper;
    }

    public function execute()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        if ($this->_coreRegistry->registry('trademe_account'))
            $this->_coreRegistry->unregister('trademe_account');
        $this->multiAccountHelper->getAccountRegistry($accountId);
        $this->helper->updateAccountVariable();
        $profileId = $this->getRequest()->getParam('profile_id');
        $catId = $this->getRequest()->getParam('catId');
        if ($catId) {
            $items = $this->getRequest()->getParam('items');
            $catIdArray = json_decode($items, true);
            end($catIdArray);
            $key = key($catIdArray);
            unset($catIdArray[$key]);
            $catIdArray[] = $catId;
            $collection = $this->_objectManager->get('Ced\TradeMe\Model\Profile')->getCollection()->addFieldToFilter('id', $profileId)->addFieldToFilter('profile_category', json_encode(array_values($catIdArray)));

            if ($collection->getSize() > 0) {
                $profile = $collection->getFirstItem();
                $this->_coreRegistry->register('current_profile', $profile);
            }
        } else {
            $catJson = $this->_objectManager->get('Ced\TradeMe\Model\Profile')->load($profileId)->getProfileCategory();
            if ($catJson) {
                $catArray = array_reverse(json_decode($catJson, true));
                foreach ($catArray as $value) {
                    if ($value != "") {
                        $catId = $value;
                        break;
                    }
                }
            }
        }
        $result = $this->resultPageFactory->create(true)->getLayout()->createBlock('Ced\TradeMe\Block\Adminhtml\Profile\Edit\Tab\Attribute\Trademeattribute')->setCatId($catId)->toHtml();
        $this->getResponse()->setBody($result);
    }
}
