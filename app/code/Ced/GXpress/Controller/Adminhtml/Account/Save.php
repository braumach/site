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

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Store\Model\StoreManagerInterface;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Class Save
 * @package Ced\GXpress\Controller\Adminhtml\Account
 */
class Save extends Action
{
    public $_mediaDirectory;
    public $_fileUploaderFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_GXpress::GXpress';

    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * Save constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param Context $context
     * @param \Ced\GXpress\Model\AccountsFactory $accounts
     * @param \Ced\GXpress\Helper\MultiAccount $multiAccountHelper
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        Context $context,
        \Ced\GXpress\Model\AccountsFactory $accounts,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        StoreManagerInterface $storeManager
    )
    {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct($context);
        $this->accounts = $accounts;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $accountDetails = $this->getRequest()->getParams();
        //$clientSecret = isset($_FILES) ? $_FILES : array();
        try {
            if (isset($accountDetails['account_code']) || isset($accountDetails['id'])) {
                if (isset($accountDetails['id'])) {
                    $accounts = $this->accounts->create()->load($accountDetails['id']);
                } else {
                    $accounts = $this->accounts->create();
                }
                if($this->getRequest()->getFiles('account_file')['name']) {
                    $target = $this->_mediaDirectory->getAbsolutePath('GXpress/');
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => 'account_file']);
                    $uploader->setAllowedExtensions(['json']);
                    $uploader->setAllowRenameFiles(false);
                    $result = $uploader->save($target);
                    $accounts->addData(["account_file" => $result['path'] . $result['file']]);
                }
                if(!isset($accountDetails['id'])) {
                    $merchantId = $accounts->getCollection()->addFieldToFilter('merchant_id',['in' => $accountDetails['merchant_id']])->getData();
                    if($merchantId) {
                        throw new \Exception('Merchant Id Already Exists');
                    }
                    $accountCode = $accounts->getCollection()->addFieldToFilter('account_code',['in' => $accountDetails['account_code']])->getData();
                    if($accountCode) {
                        throw new \Exception('Account Code Already Exists');
                    }
                }
                $accounts->addData($accountDetails)->save();
                $this->multiAccountHelper->createProfileAttribute($accounts->getId(), $accounts->getAccountCode());
                $this->messageManager->addSuccessMessage(__('Account Saved Successfully.'));
                $this->_redirect('*/*/edit', ['id' => $accounts->getId()]);
            } else {
                $this->messageManager->addNoticeMessage(__('Please fill the Account Code'));
                $this->_redirect('*/*/new');
            }
        } catch (\Exception $e) {
            $this->_objectManager->create('Ced\GXpress\Helper\Logger')->addError('In Save Account: ' . $e->getMessage(), ['path' => __METHOD__]);
            $this->messageManager->addErrorMessage(__('Unable to Save Account Details Please Try Again.' . $e->getMessage()));
            $this->_redirect('*/*/new');
        }
        return;
    }
}