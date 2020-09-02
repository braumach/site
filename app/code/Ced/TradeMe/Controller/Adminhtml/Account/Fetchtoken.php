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
 * @package   Ced_TradeMe
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\TradeMe\Controller\Adminhtml\Account;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Fetchtoken
 * @package Ced\TradeMe\Controller\Adminhtml\Account
 */
class Fetchtoken extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_TradeMe::TradeMe';
    /**
     * @var \Ced\TradeMe\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Ced\TradeMe\Helper\Logger
     */
    public $logger;
    /**
     * @var \Ced\TradeMe\Helper\MultiAccount
     */
    public $multiAccountHelper;

    /**
     * @var \Ced\TradeMe\Model\AccountsFactory
     */
    public $accounts;
    public $scopeConfig;
    public $cacheTypeList;

    /**
     * Fetchtoken constructor.
     * @param Action\Context $context
     * @param \Ced\TradeMe\Helper\Data $dataHelper
     * @param \Ced\TradeMe\Helper\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\TradeMe\Helper\Data $dataHelper,
        \Ced\TradeMe\Helper\Logger $logger,
        \Ced\TradeMe\Model\AccountsFactory $accounts,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        parent::__construct($context);
        $this->multiAccountHelper = $multiAccountHelper;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->accounts = $accounts;
        $this->scopeConfig = $scopeConfig;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            $msg = '';
            $accountData = '';
            $params = $this->getRequest()->getParams();
            if (isset($params['id']) && !empty($params['id'])) {
                $accountData = $this->accounts->create()
                    ->loadByField('id', $params['id']);

            } else {
                $data['valid'] = 0;
                $data['message'] = "Please fill the oauth_callback";
                return $this->getResponse()->setBody(json_encode($data));
            }
            $this->dataHelper->updateAccountVariable();
            $callbackUrl = 'https://demo.cedcommerce.com/magento/integrations/';
            $response = $this->dataHelper->fetchToken($accountData->getData(), $callbackUrl);

            if ($response['status'] == 'success' /*&& strpos("oauth_token=", $response['message'][0])*/) {
                $oauth_token = str_replace("oauth_token=", "", $response['message'][0]);
                $oauth_token_secret = str_replace("oauth_token_secret=", "", $response['message'][1]);

                $accountData->setOuthAccessToken($oauth_token);
                $accountData->setOuthTokenSecret($oauth_token_secret);
                $accountData->save();
                if (isset($response['url']) && $response['url'] != '')
                {
                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    $resultRedirect->setUrl($response['url']);
                    return $resultRedirect;
                }
                $data['valid'] = 1;
                $data['message'] = $response['url'];
                $msg = 'Token fetch successfully';
            } else {
                $data['valid'] = 0;
                $data['message'] = $response['message'];
                $msg = 'Credentials are Invalid';
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            $data['valid'] = 0;
            $data['message'] = $e->getMessage();
        }
        $this->messageManager->addNoticeMessage($msg);
        $this->_redirect('trademe/account/index');
    }
}