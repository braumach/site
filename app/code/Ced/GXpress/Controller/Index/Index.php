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

namespace Ced\GXpress\Controller\Index;

use \Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    /**
     * @var \Ced\GXpress\Helper\MultiAccount $multiAccHelper
     */
    public $multiAccHelper;

    /** @var \Ced\GXpress\Helper\GXpresslib $gXpressHelper */
    public $gXpressHelper;

    /** @var \Magento\Framework\Message\ManagerInterface $messageManager */
    public $messageManager;

    /** @var \Magento\Framework\UrlInterface $_urlInterface ; */
    public $_urlInterface;

    public $_coreRegistry;

    public function __construct(
        Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ced\GXpress\Helper\MultiAccount $multiAccHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\GXpress\Helper\GXpresslib $gXpressHelper
    )
    {
        $this->multiAccHelper = $multiAccHelper;
        $this->gXpressHelper = $gXpressHelper;
        $this->messageManager = $messageManager;
        $this->_urlInterface = $urlInterface;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function execute()
    {

        $admin = $this->_objectManager->create('Magento\Backend\Helper\Data')->getAreaFrontName();
        try {
            $cacheManager = $this->_objectManager->create('Magento\Framework\App\CacheInterface');
            $accountID = $cacheManager->load('gxpress_account');
            //$accountID = 1;
            //$code = '4/BQH-x-E5M6M1R_cWstgKnDG1HPGrTyL7IWiFZ1c0-ZFtItBsgf2TeNgYXGkTn3zLsduvVhI-xqRWCpmy-x8ID8A';
            $account = $this->multiAccHelper->getAccountRegistry($accountID);
            $client = $this->gXpressHelper->getGoogleClient();
            $client->authenticate(/*$code*/$this->getRequest()->getParam('code'));
            $refreshToken = $client->getAccessToken()['refresh_token'];
            if ($account && $account->getId()) {
                $account->setAccountToken($refreshToken)->save();
            }
            $this->messageManager->addSuccess("Token for Googleexpress Account " . $account->getAccountCode() . " has been fetched successfully.");

            $url = $this->_urlInterface->getUrl($admin.'/gxpress/account/index');
            $this->getResponse()->setRedirect($url)->sendResponse();
        } catch(\Exception $e) {
            $this->messageManager->addSuccess("Token for Googleexpress Account " . $account->getAccountCode() . " fetching failed. ".$e->getMessage());
            $url = $this->_urlInterface->getUrl($admin.'/gxpress/account/index');
            $this->getResponse()->setRedirect($url)->sendResponse();
        }

    }
}