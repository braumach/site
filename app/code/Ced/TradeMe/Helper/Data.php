<?php


namespace Ced\TradeMe\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Ced\TradeMe\Helper\Logger;



class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const API_URL_SANDBOX = "https://api.tmsandbox.co.nz/v1/";
    const API_URL = "https://api.trademe.co.nz/v1/";

    const OAUTH_REQUEST_TOKEN_URL_SANDBOX = "https://secure.tmsandbox.co.nz/Oauth/RequestToken"; //?scope=MyTradeMeRead,
    const OAUTH_REQUEST_TOKEN_URL = "https://secure.trademe.co.nz/Oauth/RequestToken"; //?scope=MyTradeMeRead,
    //MyTradeMeWrite

    const OAUTH_AUTHORISE_TOKEN_URL_SANDBOX = "https://secure.tmsandbox.co.nz/Oauth/Authorize";//?oauth_token=  also get
    // oauth_verifier
    const OAUTH_AUTHORISE_TOKEN_URL = "https://secure.trademe.co.nz/Oauth/Authorize";//?oauth_token=  also get
    // oauth_verifier

    const OAUTH_ACCESS_TOKEN_URL_SANDBOX =  "https://secure.tmsandbox.co.nz/Oauth/AccessToken";
    const OAUTH_ACCESS_TOKEN_URL =  "https://secure.trademe.co.nz/Oauth/AccessToken";
    protected $scopeConfig;
    public $apiMode;
    public $apiUrl;

    public $fileIo;
    public $_allowedFeedType = array();

    /**
     * @var mixed
     */
    public $permissions;

    /**
     * @var mixed
     */
    public $oauthCallback;

    /**
     * @var mixed
     */
    public $oauthConsumerKey;

    /**
     * @var mixed
     */
    public $oauthConsumerSecret;

    /**
     * @var mixed
     */
    public $oauthToken;

    /**
     * @var mixed
     */
    public $oauthTokenSecret;

    /**
     * @var string
     */
    public $requestTokenUrl;

    /**
     * @var string
     */
    public $authoriseTokenUrl;

    /**
     * @var string
     */
    public $accessTokenUrl;

    /**
     * @var array
     */
    public $authParams;
    public $directoryList;
    public $json;
    public $adminSession;
    public $multiAccountHelper;
    public $registry;
    public $logger;


    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Backend\Model\Session $session,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper,
        \Magento\Framework\Registry $registry,
        Logger $logger,
        DirectoryList $directoryList
    )
    {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->adminSession = $session;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->_coreRegistry = $registry;
        $this->logger = $logger;


        $this->apiUrl = $this->apiMode == 'sandbox' ? self::API_URL_SANDBOX :self::API_URL;
        $this->accessTokenUrl = $this->apiMode == 'sandbox' ? self::OAUTH_ACCESS_TOKEN_URL_SANDBOX
            :self::OAUTH_ACCESS_TOKEN_URL;

    }


    public function getRequest($url, $authentication = false)
    {
        $request = null;
        $response = null;
        try {
            $request = curl_init();
            if ($authentication){

                $randomString = rand(10,100);
                $header[] = "Content-Type: application/json";
                $header[] = "Authorization: OAuth oauth_consumer_key=".$this->oauthConsumerKey.",
                 oauth_token=".$this->oauthToken.",
                  oauth_version=1.0,
                   oauth_timestamp=".time().",
                    oauth_nonce=ced".$randomString.",
                     oauth_signature_method=PLAINTEXT,
                      oauth_signature=".$this->oauthConsumerSecret."&".$this->oauthTokenSecret;
                curl_setopt($request, CURLOPT_HTTPHEADER, $header);
            }

            curl_setopt($request, CURLOPT_URL, $url);
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($request);
            $errors = curl_error($request);
            if (!empty($errors)) {
                curl_close($request);
                throw new \Exception($errors);
            }
            curl_close($request);
            return $response;
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);

            return false;
        }
    }

    public function postRequest($url, $data=array())
    {

        $request = null;
        $response = null;
        try {
            $randomString = rand(10,100);
            $header[] = "Content-Type: application/json";
            $header[] = "Authorization: OAuth oauth_consumer_key=".$this->oauthConsumerKey.",
                 oauth_token=".$this->oauthToken.",
                  oauth_version=1.0,
                   oauth_timestamp=".time().",
                    oauth_nonce=ced".$randomString.",
                     oauth_signature_method=PLAINTEXT,
                      oauth_signature=".$this->oauthConsumerSecret."&".$this->oauthTokenSecret;
                       /*echo "<pre>";print_r($url);
            print_r($header);
            var_dump(json_decode(json_encode($data), true));die;*/
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $servererror = curl_error($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($response, $header_size);
            if (!empty($servererror)) {
                $request = curl_getinfo($ch);
                curl_close($ch);
                throw new \Exception($servererror);
            }
            curl_close($ch);
            return $body;
        } catch(\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            return false;
        }
    }

    public function fetchToken($params)
    {
        $response = [];
        $this->requestTokenUrl = $params['account_env'] == 'sandbox' ? self::OAUTH_REQUEST_TOKEN_URL_SANDBOX :
            self::OAUTH_REQUEST_TOKEN_URL;
        $this->authoriseTokenUrl = $params['account_env'] == 'sandbox' ? self::OAUTH_AUTHORISE_TOKEN_URL_SANDBOX
            :self::OAUTH_AUTHORISE_TOKEN_URL;
        try {
            $randomString = rand(10,100);
            $header[] = "Content-Type: application/json";
            $header[] = "Authorization: OAuth 
                oauth_consumer_key=".$params['outh_consumer_key'].",
                oauth_version=1.0,
                oauth_timestamp=".time().",
                oauth_nonce=ced.".$randomString.",
                oauth_signature_method=PLAINTEXT,
                oauth_signature=".$params['outh_consumer_secret']."&";
            $ch = curl_init();
            $url = $this->requestTokenUrl."?scope=MyTradeMeRead,MyTradeMeWrite,BiddingAndBuying";

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt ($ch, CURLOPT_POST, 1 );
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($server_output, $header_size);
            $this->adminSession->setSessId($params['id']);
            curl_close($ch);
            if (isset($body)) {
                $match=strpos($body,'oauth_token_secret=');
                if(isset($match) && $match!=0){
                    $authResponse = explode('&', $body);
                    if (isset($authResponse[0])) {
                        $response['status'] = 'success';
                        $response['message'] = $authResponse;
                        $response['url'] = $this->authoriseTokenUrl . "?".$authResponse[0];
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = "please check the credentials";
                    }
                }else{
                    $response['status'] = 'error';
                    $response['message'] = "please check the credentials";
                }
            }

        }catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function validateToken($params)
    {
        try {
            $randomString = rand(10,100);
            $header[] = "Content-Type: application/json";
            $header[] = "Authorization: OAuth oauth_verifier=".$params['outh_verifier'].",
                oauth_consumer_key=".$this->oauthConsumerKey.",
                oauth_token=".$this->oauthToken.",
                oauth_version=1.0,
                oauth_timestamp=".time().",
                oauth_nonce=ced.".$randomString.",
                oauth_signature_method=PLAINTEXT,
                oauth_signature=".$this->oauthConsumerSecret."&".$this->oauthTokenSecret;

            $ch = curl_init();
            $url = $this->accessTokenUrl;

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt ($ch, CURLOPT_POST, 1 );
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($server_output, $header_size);
            curl_close($ch);

            $authResponse = explode('&', $body);
            if (isset($authResponse[0]) && isset($authResponse[1])) {
                $oauth_token = str_replace("oauth_token=", "", $authResponse[0]);
                $tokenSecretWithMsg=  str_replace("oauth_token_secret=", "", $authResponse[1]);
                $oauth_token_secret = explode('{', $tokenSecretWithMsg);
                $finalResponse = array('oauth_token' => $oauth_token, 'oauth_token_secret' => $oauth_token_secret[0]);
                $response['status'] = 'success';
                $response['message'] = $finalResponse;
            } else {
                $response['status'] = 'error';
                $response['message'] = $authResponse[0];
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function getOrders()
    {
        $response = array();
        try {
            $orderFilter =$this->scopeConfig->getValue('trademe_config/order/order_filter');
            $url = $this->apiUrl."MyTradeMe/SoldItems/".$orderFilter.".json";
            $randomString = rand(10,100);
            $header[] = "Content-Type: application/json";
            $header[] = "Authorization: OAuth oauth_consumer_key=".$this->oauthConsumerKey.",
                 oauth_token=".$this->oauthToken.",
                  oauth_version=1.0,
                   oauth_timestamp=".time().",
                    oauth_nonce=ced".$randomString.",
                     oauth_signature_method=PLAINTEXT,
                      oauth_signature=".$this->oauthConsumerSecret."&".$this->oauthTokenSecret;
            /*print_r($url);
            print_r($header);die;*/
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $servererror = curl_error($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($response, $header_size);
            curl_close($ch);

            $response = json_decode($body, true);
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            $response['errors'] = true;
            $response['data'] = $e->getMessage();
        }
        return $response;
    }


    public function fetchAllCategories()
    {
        $url = $this->apiUrl."Categories.json";
        $jsonResponse = $this->getRequest($url);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function loadFile($path, $code = '', $type = '')
    {
        if (!empty($code)) {
            $path = $this->directoryList->getPath($code) . "/" . $path;
        }
        if (file_exists($path)) {
            $pathInfo = pathinfo($path);
            if ($pathInfo['extension'] == 'json') {
                $myfile = fopen($path, "r");
                $data = fread($myfile, filesize($path));
                fclose($myfile);
                if (!empty($data)) {
                    $data = empty($type) ? $this->json->jsonDecode($data) : $data;
                    return $data;
                }
            }
        }
        return false;
    }
    public function fetchCatAttr($catId)
    {
        $url = $this->apiUrl."Categories/".$catId."/Details.json";
        $jsonResponse = $this->getRequest($url);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function setAccountSession() {
        $accountId = '';
        $this->adminSession->unsAccountId();
        $params = $this->_getRequest()->getParams();
        if(isset($params['account_id']) && $params['account_id'] > 0) {
            $accountId = $params['account_id'];
        } else {
            $accountId = $this->scopeConfig->getValue('trademe_config/trademe_setting/primary_account');
            if(!$accountId) {
                $accounts = $this->multiAccountHelper->getAllAccounts(true);
                if($accounts) {
                    $accountId = $accounts->getFirstItem()->getId();
                }
            }
        }
        $this->adminSession->setAccountId($accountId);
        return $accountId;
    }

    public function getAccountSession() {
        $accountId = '';
        $accountId = $this->adminSession->getAccountId();
        if(!$accountId) {
            $accountId = $this->setAccountSession();
        }
        return $accountId;
    }

    public function updateAccountVariable()
    {
        $account = false;
        if ($this->_coreRegistry->registry('trademe_account')) {
            $account = $this->_coreRegistry->registry('trademe_account');
        }
        $this->apiMode = ($account) ? trim($account->getAccountEnv()) : '';
        $this->oauthToken = ($account) ? trim($account->getOuthAccessToken()) : '';
        $this->oauthTokenSecret = ($account) ? trim($account->getOuthTokenSecret()) : '';
        $this->oauthConsumerKey = ($account) ? trim($account->getOuthConsumerKey()) : '';
        $this->oauthConsumerSecret = ($account) ? trim($account->getOuthConsumerSecret()) : '';
        $this->accessTokenUrl = ($account) ? trim($account->getAccountEnv()) : '';
        $this->accessTokenUrl = (($account) ? trim($account->getAccountEnv()) : '') == 'sandbox' ? self::OAUTH_ACCESS_TOKEN_URL_SANDBOX
            :self::OAUTH_ACCESS_TOKEN_URL;
        $this->apiUrl = (($account) ? trim($account->getAccountEnv()) : '') == 'sandbox' ? self::API_URL_SANDBOX
            :self::API_URL;

    }

    public function productUpload($value)
    {
        $url = $this->apiUrl.'Selling.json';
        $jsonResponse = $this->postRequest($url, $value);
        $response = json_decode($jsonResponse, true);
        return $response;
    }
    public function productSync($value)
    {
        $url = $this->apiUrl.'Selling/Edit.json';
        $jsonResponse = $this->postRequest($url, $value);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function getProductData($listingId)
    {
        $url = $this->apiUrl.'Listings/'.$listingId.'.json';
        $jsonResponse = $this->getRequest($url, true);
        $response = json_decode($jsonResponse, true);
        return $response;
    }
    public function imageUpload($value)
    {
        /*$url =self::API;*/
        $url = $this->apiUrl.'Photos/Add.json';
        $jsonResponse = $this->postRequest($url, $value);

        $response = json_decode($jsonResponse, true);
        return $response;
    }
    public function updatePhoto($ids,$listingId){
        $url = $this->apiUrl;
        $url = $url.'Photos/'.$ids.'/Add/'.$listingId.'.json';
        $jsonResponse = $this->postRequest($url);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function withdrawAuction($value)
    {
        $url = $this->apiUrl.'Selling/Withdraw.json';
        $jsonResponse = $this->postRequest($url, $value);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function productRelist($value)
    {
        $url = $this->apiUrl.'Selling/Relist.json';
        $jsonResponse = $this->postRequest($url, $value);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function createShipmentOrderBody($value)
    {
        $url = $this->apiUrl.'MyTradeMe/CourierParcels.json';
        $jsonResponse = $this->postRequest($url, $value);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

}