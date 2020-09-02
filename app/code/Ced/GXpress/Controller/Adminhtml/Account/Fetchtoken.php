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

namespace Ced\GXpress\Controller\Adminhtml\Account;

use Magento\Backend\App\Action;

if (!defined('DIRECTORY_SEPARATOR')) {
    define('DIRECTORY_SEPARATOR', DIRECTORY_SEPARATOR);
}

/**
 * Class Fetchtoken
 * @package Ced\GXpress\Controller\Adminhtml\Account
 */
class Fetchtoken extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_GXpress::GXpress';
    /**
     * @var \Ced\GXpress\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Ced\GXpress\Helper\Logger
     */
    public $logger;
    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    public $multiAccountHelper;


    /** @var \Magento\Framework\Message\ManagerInterface $messageManager */
    public $messageManager;
    /**
     * @var \Ced\GXpress\Model\AccountsFactory
     */
    public $accounts;

    /** @var \Ced\GXpress\Helper\GXpresslib $gXpressHelper */
    public $gXpressHelper;

    /** @var \Magento\Framework\Filesystem\DirectoryList $dir */
    public $dir;

    /** @var \Magento\Framework\Registry $_coreRegistry */
    public $_coreRegistry;

    /**
     * Fetchtoken constructor.
     * @param Action\Context $context
     * @param \Ced\GXpress\Helper\Data $dataHelper
     * @param \Ced\GXpress\Helper\Logger $logger
     * @param \Ced\GXpress\Model\AccountsFactory $accounts
     * @param \Ced\GXpress\Helper\MultiAccount $multiAccountHelper
     * @param \Ced\GXpress\Helper\GXpresslib $gXpressHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\GXpress\Helper\Data $dataHelper,
        \Ced\GXpress\Helper\Logger $logger,
        \Ced\GXpress\Model\AccountsFactory $accounts,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        \Ced\GXpress\Helper\GXpresslib $gXpressHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        parent::__construct($context);
        $this->multiAccountHelper = $multiAccountHelper;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->accounts = $accounts;
        $this->gXpressHelper = $gXpressHelper;
        $this->_coreRegistry = $coreRegistry;
        $this->messageManager = $messageManager;
        $this->dir = $dir;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $data = $this->getRequest()->getParams();
            if (isset($data['id'])) {
                $this->multiAccountHelper->getAccountRegistry($data['id']);
                $cacheManager = $this->_objectManager->create('Magento\Framework\App\CacheInterface');
                $cacheManager->save($data['id'], 'gxpress_account');
                $this->dataHelper->updateAccountVariable();
                $accounts = $this->accounts->create()->load($data['id']);
            }

            if ($accounts) {
                $accountStatus = $accounts->getAccountStatus();
                if($accountStatus) {
                    $secretJson = $accounts->getAccountFile();
                    $secretKey = file_get_contents($secretJson);
                    $secretKey = @json_decode($secretKey, true);
                    if (!isset($secretKey['web'])) {
                        $message = "WRONG JSON!! Please enter correct Json";
                        $this->messageManager->addError($message);
                        return $this->resultRedirectFactory->create()->setPath('*/*/index');
                    }
                } else {
                    $message = $accounts->getAccountCode()." Account Disabled!! Please enable the account and refetch the token.";
                    $this->messageManager->addError($message);
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }

            }

            $client = $this->_objectManager->create('Ced\GXpress\Helper\GXpresslib')->getGoogleClient();
            if($client) {
                $client->setApprovalPrompt('force');
                $auth_url = $client->createAuthUrl();
                return $this->resultRedirectFactory->create()->setPath($auth_url);
            }
            $message = "WRONG JSON!! Please enter correct Json";
            $this->messageManager->addError($message);
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        } catch (\Exception $e) {
            $message = "WRONG JSON!! Please enter correct Json";
            $this->messageManager->addError($message);
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }
    }
}