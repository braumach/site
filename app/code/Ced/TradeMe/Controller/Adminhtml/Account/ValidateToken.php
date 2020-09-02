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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\TradeMe\Controller\Adminhtml\Account;

use Ced\TradeMe\Helper\MultiAccount;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Ced\TradeMe\Controller\Adminhtml\Account
 */
class ValidateToken extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_TradeMe::TradeMe';
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;
    public $adminSession;
    public $accountsFactory;
    /**
     * @var \Ced\TradeMe\Helper\Data
     */
    public $datahelper;
    /**
     * @var MultiAccount
     */
    public $multiAccount;

    /**
     * ValidateToken constructor.
     * @param Context $context
     * @param \Magento\Backend\Model\Session $session
     * @param \Ced\TradeMe\Model\AccountsFactory $accountsFactory
     * @param \Ced\TradeMe\Helper\Data $datahelper
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Backend\Model\Session $session,
        \Ced\TradeMe\Model\AccountsFactory $accountsFactory,
        \Ced\TradeMe\Helper\MultiAccount $multiAccount,
        \Ced\TradeMe\Helper\Data $datahelper,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->adminSession = $session;
        $this->multiAccount = $multiAccount;
        $this->datahelper = $datahelper;
        $this->accountsFactory = $accountsFactory;
    }


    public function execute()
    {
        $data = $this->getRequest()->getParams();

        $accountId = $this->adminSession->getSessId();

        $account = $this->accountsFactory->create()->load($accountId);

        $this->multiAccount->getAccountRegistry($accountId);
        $this->datahelper->updateAccountVariable();

        $account->setOuthVerifier($data['oauth_verifier']);
        $account->save();
        $response = $this->datahelper->validateToken($account->getData());
        if (isset($response['status']) && $response['status'] == 'success') {
            $account->setOuthAccessToken($response['message']['oauth_token']);
            $account->setOuthTokenSecret($response['message']['oauth_token_secret']);
            $account->save();

            $this->messageManager->addSuccessMessage(__('Token Fetched Successfully.'));
            $this->_redirect('*/*/index');
        } else {
            $account->setOuthAccessToken('');
            $account->setOuthTokenSecret('');
            $account->save();
            $this->messageManager->addErrorMessage(__($response['message']));
            $this->_redirect('*/*/index');

        }
    }
}