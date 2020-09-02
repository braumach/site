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

namespace Ced\GXpress\Helper;

use Magento\Framework\App\Helper\Context;
use \Ced\GXpress\Helper\Logger;

class GXpresslib extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $multiAccountHelper;
    public $scopeConfig;
    public $debugMode;
    public $_objectManager;
    public $contentLanguage;
    public $targetCountry;
    public $includeDestination;
    public $_storeManager;
    public $logger;

    /*const GOOGLEEXPRESS_FETCH_CODE = 'gxpress_configuration/gxpresssetting/auth_code';*/
    const GXPRESS_FETCH_TOKEN = 'gxpress_configuration/gxpresssetting/token';
    const GXPRESS_API_URL = 'https://www.googleapis.com/content/';
    const CONFIG_PATH_MERCHANT_ID = 'gxpress_configuration/gxpresssetting/gxpress_merchantId';
    const GOOGLE_REDIRECT_URI = 'gxpress_configuration/gxpresssetting/gxpress_admin_website';
    const GXPRESS_DEBUGMODE = 'gxpress_config/product_upload/debugmode';
    const CONFIG_PATH_PRODUCT_DESTINATION = 'gxpress_config/product_upload/included_destination';
    const CONFIG_PATH_PRODUCT_TARGETCOUNTRY = 'gxpress_config/product_upload/target_country';
    const CONFIG_PATH_PRODUCT_CONTENTLANGUAGE = 'gxpress_config/product_upload/content_language';


    public function __construct(
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Logger $logger,
        Context $context
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->scopeConfig = $scopeConfig;
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        /** @var Ced_GXpress_Helper_Config $config */
        //$config = $this->_objectManager->create('Ced\GXpress\Helper\Config');
        $this->debugMode = $this->scopeConfig->getValue(self::GXPRESS_DEBUGMODE, $storeScope);
        $this->logger = $logger;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->includeDestination = $this->scopeConfig->getValue(self::CONFIG_PATH_PRODUCT_DESTINATION);
        $this->targetCountry = $this->scopeConfig->getValue(self::CONFIG_PATH_PRODUCT_TARGETCOUNTRY);
        $this->contentLanguage = $this->scopeConfig->getValue(self::CONFIG_PATH_PRODUCT_CONTENTLANGUAGE);
    }

    public function uploadProductOnGXpress($productToUpload)
    {
        try {
            $errors = array();
            $googleClient = $this->authClient();
            $service = '';
            $response = '';
            if (!is_bool($googleClient)) {
                $service = new \Google_Service_ShoppingContent($googleClient);
                $product = $this->prepareCustomBatch($productToUpload);
                $entries = new \Google_Service_ShoppingContent_ProductsCustomBatchRequest();
                $entries->setEntries($product);
                $response = $service->products->custombatch($entries);
                foreach ($response->entries as $entry) {
                    if (isset($entry->errors) && isset($entry->errors->errors)) {
                        foreach ($entry->errors->errors as $error) {
                            if (isset($product[$entry->batchId - 1]['product']['id'])) {
                                $errors[$product[$entry->batchId - 1]['product']['id']][] = $error->message;
                            }
                        }
                    }
                }
                if (count($errors) > 0) {
                    throw new \Exception('Upload has errors');
                }
            } else {
                return false;
            }
            return $response;
        } catch (\Exception $e) {
            echo "<pre>";
            print_r($e->getMessage());
            die("CHeck");
            if ($this->debugMode) {
                $account = $this->multiAccountHelper->getAccountRegistry();
                //$account = $this->multiAccountHelper->getAccountRegistry();
                $accountID = ($account) ? $account->getId() : '';
                $feedModel = $this->_objectManager->get('Ced\GXpress\Model\Feeds');
                $feedModel->setData('items_received', json_encode($product));
                $feedModel->setData('feed_errors', json_encode($errors));
                //$feedModel->setData('account_id', $accountID);
                $feedModel->save();
                $this->logger(
                    'Upload Product-' . json_encode($product),
                    'Response (Post Request)', $e->getMessage(),
                    true);
            }
            return false;
        }
    }

    public function authClient()
    {
        try {
            $account = $this->multiAccountHelper->getAccountRegistry();
            $client = $this->getGoogleClient();
            $token = ($account) ? $account->getAccountToken() : $this->scopeConfig(self::GXPRESS_FETCH_TOKEN);
            $client->refreshToken($token);
            return $client;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Client Error-' . $e->getMessage(),
                    'Response (Post Request)' . $e->getMessage());
            }
            return false;
        }

    }

    public function getStatusOfProducts()
    {
        $response = '';
        try {
            $googleClient = $this->authClient();
            $service = '';
            if (!is_bool($googleClient)) {
                $service = new \Google_Service_ShoppingContent($googleClient);
                $account = $this->multiAccountHelper->getAccountRegistry();
                $merchantId = ($account) ? $account->getMerchantId() :
                    $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
                $response = $service->datafeeds->listDatafeeds($merchantId);
                return $response;
            }
            return false;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Client Error-' . json_encode($response),
                    'Response (Post Request)' . json_encode($e->getMessage()));
            }
            return false;
        }
    }

    public function getGoogleClient()
    {
        $secretFile = '';
        try {
            $account = $this->multiAccountHelper->getAccountRegistry();
            if ($account) {
                $secretFile = $account->getaccountFile();
            }
            $client = new \Google_Client();
            $client->setAuthConfigFile($secretFile);
            $redirectUri = $this->_urlBuilder->getBaseUrl() . 'gxpress/index';
            $client->setRedirectUri($redirectUri);
            $client->addScope(\Google_Service_ShoppingContent::CONTENT);
            $client->setAccessType("offline");
            return $client;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Fetch Product error', 'error ' . json_encode($e->getMessage()));
            }
        }
    }

    /**
     * @param string $type
     * @param string $subType
     * @param array $response
     * @param string $comment
     * @param bool $forcedLog
     * @return bool
     */
    public function logger($type = "Test", $subType = "Test", $response = array(), $comment = "", $forcedLog = false, $mode = null)
    {
        if ($this->debugMode || $forcedLog) {
            if ($mode = 'info') {
                $this->_objectManager->get('Ced\GXpress\Helper\Logger')
                    ->addInfo($type . json_encode($response), ['path' => __METHOD__]);
            } else {
                $this->_objectManager->get('Ced\GXpress\Helper\Logger')
                    ->addError($type . json_encode($response), ['path' => __METHOD__]);
            }
            return true;
        }

        return false;
    }

    public function prepareCustomBatch($productToUpload)
    {
        $count = 1;
        $account = $this->multiAccountHelper->getAccountRegistry();

        $merchantId = ($account) ? $account->getMerchantId() : $this->getStoreConfig(self::CONFIG_PATH_MERCHANT_ID);
        $product = array();

        foreach ($productToUpload as $key => $value) {
            if (isset($productToUpload[$key]) && $productToUpload[$key] !== '') {
                if ($key == 'id') {
                    $product['offerId'] = $value;
                    continue;
                }
                if ($key == 'condition') {
                    $product[$key] = strtolower($value);
                    continue;
                }
                if ($key == 'productIdType') {
                    $product['gtin'] = $productToUpload['productId'];
                    continue;
                }

                if ($key == 'productId') {
                    $product['gtin'] = $productToUpload['productId'];
                    continue;
                }

                if ($key == 'shipping' || $key == 'sizes') {
                    $value = array($value);
                }

                if ($key == 'sellOnGoogleQuantity') {
                    $product['customAttributes'] = array(
                        [
                            "name" => "sell on google quantity",
                            "type" => "int",
                            "value" => $value
                        ]
                    );
                    continue;
                }

                $product[$key] = $value;
            }
        }
        $product['channel'] = 'online';
        $product['identifierExists'] = true;
        $productChunk[] = array(
            'method' => 'insert',
            'merchantId' => $merchantId,
            'batchId' => $count++,
            'product' => $product
        );
        return $productChunk;
    }

    public function getProductFromGoogleExpress()
    {
        try {
            $googleClient = $this->authClient();
            $merchantId = $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
            $service = '';
            if (!is_bool($googleClient)) {
                $service = new \Google_Service_ShoppingContent($googleClient);
            }
            $products = $service->products->listProducts($merchantId);
            return $products;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Fetch Product error', 'error ' . json_encode($e->getMessage()));
            }
            return false;
        }

    }

    public function fetchOrderFromGXpress()
    {
        try {
            $googleClient = $this->authClient();
            $account = $this->multiAccountHelper->getAccountRegistry();
            $sandBox = $account->getAccountEnv();
            $merchantId = ($account) ? $account->getMerchantId() : $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
            $service = '';
            $order = 'please fetch the token';
            if (!is_bool($googleClient) && $sandBox == "production") {
                $service = new \Google_Service_ShoppingContent($googleClient);
                $listOrder['statuses'] = array("active", /*"completed",*/
                    "inProgress");
                $order = $service->orders->listOrders($merchantId, $listOrder);
            } else if (!is_bool($googleClient) && $sandBox == "sandbox") {
                $service = new \Google_Service_ShoppingContent($googleClient);
                $order = $service->orders->gettestordertemplate($merchantId, "template1");
            }
            return $order;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger->addError('Fetch Order error '.$e->getMessage(), ['path' => __METHOD__]);
            }
            return false;
        }

    }

    public function orderAcknowledgement($purchaseOrderid)
    {
        try {
            $orderAck = new \Google_Service_ShoppingContent_OrdersAcknowledgeRequest();
            $orderAck->setOperationId($purchaseOrderid);
            return $orderAck;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Order Acknowledgement error', ['path' => __METHOD__]);
            }
            return false;
        }

    }

    public function getProductFromGoogleExpressById($sku)
    {
        try {
            $googleClient = $this->authClient();
            $service = '';
            if (!is_bool($googleClient)) {
                $service = new \Google_Service_ShoppingContent($googleClient);
            }
            $account = $this->multiAccountHelper->getAccountRegistry();
            $merchantId = ($account) ? $account->getMerchantId() : $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
                ->loadByAttribute('sku', $sku);
            $productId = 'online:' . $this->contentLanguage . ':' . $this->targetCountry . ':' . $product->getSku();
            $product = $service->products->get($merchantId, $productId);
            return $product;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Fetch Product By Id-' . $product, 'Sku ' . $sku);
            }
            return false;
        }
    }

    public function getFeeds()
    {
        try {
            $googleClient = $this->authClient();
            $service = '';
            if (!is_bool($googleClient)) {
                $service = new \Google_Service_ShoppingContent($googleClient);
            }
            $account = $this->multiAccountHelper->getAccountRegistry();
            $merchantId = ($account) ? $account->getMerchantId() : $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
            $response = $service->datafeeds->listDatafeeds($merchantId);
            return $response;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Fetch Feed Error ', json_encode($e->getMessage()));
            }
            return false;
        }

    }

    public function deleteRequest($id)
    {
        try {
            $googleClient = $this->authClient();
            $account = $this->multiAccountHelper->getAccountRegistry();
            $merchantId = ($account) ? $account->getMerchantId() : $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
            $service = '';
            $sku = '';
            if (!is_bool($googleClient)) {
                $service = new \Google_Service_ShoppingContent($googleClient);
                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
                    ->load($id);
                $sku = $product->getSku();
                $googleid = 'online:' . $this->contentLanguage . ':' . $this->targetCountry . ':' . $sku;
                $res = $service->products->delete($merchantId, $googleid);
                $response['type'] = 'success';
                $response['data'] = $sku;
                return $response;
            }
            $response['data'] = $sku;
            return $response;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Delete Requset-' . $googleid, 'Sku ' . $sku);
            }
            $msg = json_decode($e->getMessage(), true);
            $code = $msg['error']['code'];
            $msgs = $msg['error']['message'];
            if ($code == 404) {
                $response['data'] = $sku . ' ' . $msgs;
            } else {
                $response['data'] = $sku;
            }
            return $response;
        }

    }

    public function UpdateInventory($ids)
    {
        try {
            $googleClient = $this->authClient();
            $account = $this->multiAccountHelper->getAccountRegistry();
            $merchantId = ($account) ? $account->getMerchantId() : $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
            $service = '';
            $response = array();
            if (!is_array($ids)) {
                $ids = array($ids);
            }
            if (!is_bool($googleClient)) {
                foreach ($ids as $id) {
                    try {
                        $service = new \Google_Service_ShoppingContent($googleClient);
                        //$inventory = new \Google_Service_ShoppingContent_PosInventoryRequest();//InventorySetRequest();
                        $inventory = new \Google_Service_ShoppingContent_InventorySetRequest();
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
                            ->load($id);
                        /*$stock = $this->_objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface')
                            ->getStockQty($product->getId(), $product->getStore()->getWebsiteId());*/
                        $stock = $product->getData('quantity_and_stock_status');
                        $stock = $stock['qty'];
                        $sku = $product->getData('sku');
                        $googleId = 'online:' . $this->contentLanguage . ':' . $this->targetCountry . ':' . $sku;
                        if ($stock) {
                            $inventory->setSellOnGoogleQuantity($stock);
                            $inventory->setAvailability('in stock');
                        } else {
                            $inventory->setSellOnGoogleQuantity(0);
                            $inventory->setAvailability('out of stock');
                        }
                        $res = $service->inventory->set($merchantId, 'online', $googleId, $inventory);
                        $response['type'] = 'success';
                        $response['data'] = $sku;
                    } catch (\Exception $e) {
                        $response['data'] = $sku;
                        continue;
                    }
                }
                return $response;
            }
            return false;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Inventory Update-' . json_encode($e->getMessage()), 'Id ' . json_encode($id));
            }
            return false;
        }
    }

    public function updatePriceInventory($ids)
    {
        try {
            $googleClient = $this->authClient();
            $account = $this->multiAccountHelper->getAccountRegistry();
            $merchantId = ($account) ? $account->getMerchantId() : $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
            $service = '';
            $response = array();
            if (!is_array($ids)) {
                $ids = array($ids);
            }
            if (!is_bool($googleClient)) {
                foreach ($ids as $id) {
                    try {
                        $service = new \Google_Service_ShoppingContent($googleClient);
                        $inventory = new \Google_Service_ShoppingContent_InventorySetRequest();
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
                            ->load($id);
                        /*$stock = $this->_objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface')
                            ->getStockQty($product->getId(), $product->getStore()->getWebsiteId());*/
                        $stock = $product->getData('quantity_and_stock_status');
                        $stock = $stock['qty'];
                        $sku = $product->getSku();
                        $googleId = 'online:' . $this->contentLanguage . ':' . $this->targetCountry . ':' . $sku;
                        if ($stock) {
                            $inventory->setSellOnGoogleQuantity($stock);
                            $inventory->setAvailability('in stock');
                        } else {
                            $inventory->setSellOnGoogleQuantity(0);
                            $inventory->setAvailability('out of stock');
                        }
                        $price = new \Google_Service_ShoppingContent_Price();
                        $price->setValue($product->getPrice());
                        $currency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
                        $price->setCurrency(isset($currency) ? $currency : 'USD');
                        $inventory->setPrice($price);
                        $res = $service->inventory->set($merchantId, 'online', $googleId, $inventory);
                        $response['type'] = 'success';
                        $response['data'] = $sku;
                    } catch (\Exception $e) {
                        $response['data'] = $sku;
                        continue;
                    }
                }
                return $response;
            }
            return false;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Inventory Update-' . json_encode($e->getMessage()), 'Id ' . json_encode($ids));
            }
            return false;
        }
    }

    public function updatePrice($skus)
    {
        try {
            $googleClient = $this->authClient();
            $account = $this->multiAccountHelper->getAccountRegistry();
            $merchantId = ($account) ? $account->getMerchantId() : $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
            $service = '';
            $response = array();
            if (!is_bool($googleClient)) {
                foreach ($skus as $sku) {
                    try {
                        $service = new \Google_Service_ShoppingContent($googleClient);
                        $price = new \Google_Service_ShoppingContent_InventorySetRequest();
                        $priceObj = new \Google_Service_ShoppingContent_Price();
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
                            ->loadByAttribute('sku', $sku);
                        $priceObj->setValue($product->getPrice());
                        $currency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
                        $priceObj->setCurrency(isset($currency) ? $currency : 'USD');
                        $price->setPrice($priceObj);
                        $googleId = 'online:' . $this->contentLanguage . ':' . $this->targetCountry . ':' . $sku;
                        $res = $service->inventory->set($merchantId, 'online', $googleId, $price);
                        $response['type'] = 'success';
                        $response['data'] = $sku;
                    } catch (\Exception $e) {
                        $response['data'] = $sku;
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Price Update-' . json_encode($service), 'Sku ' . $sku);
            }
            return false;
        }
        return $response;
    }

    public function updateOrderStatus($data_ship)
    {
        $response = array();
        try {
            if ($data_ship['noCallToGenerateShipment']) {

                //$shipData = array();
                foreach ($data_ship['shipments'] as $key => $value) {
                    $purchaseOrderId = $data_ship['shipments'][$key]['purchaseOrderId'];
                    $shipmentTrackingNumber = $data_ship['shipments'][$key]['shipment_tracking_number'];
                    $carrier = $data_ship['shipments'][$key]['carrier'];
                    //$shipData['operationId'] = $purchaseOrderId;
                    /*$shipData['carrier'] = $carrier;
                    $shipData['trackingId'] = $shipmentTrackingNumber;*/

                    foreach ($value['shipment_items'] as $shipmentKey => $shipmentValue) {
                        $shipmentId = $shipmentValue['shipment_item_id'];
                        break;
                    }
                }

                $orderData = $this->fetchOrderFromGoogleExpressByOrderId($purchaseOrderId);
                $lineItem = array();
                foreach ($orderData->getLineItems() as $key => $value) {
                    $lineItem[] = array(
                        'lineItemId' => $value->getId(),
                        'quantity' => $value->getQuantityOrdered()
                    );
                }

                //$shipData['lineItems'] = $lineItem;
                $googleClient = $this->authClient();
                $account = $this->multiAccountHelper->getAccountRegistry();
                $merchantId = ($account) ? $account->getMerchantId() : $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
                $orderObj = new \Google_Service_ShoppingContent_OrdersShipLineItemsRequest();
                $orderObj->setOperationId($purchaseOrderId);

                $shipmentInfos[] = [
                    "shipmentId" => $shipmentId,
                    "trackingId" => $shipmentTrackingNumber,
                    "carrier" => $carrier
                ];
                $this->logger('Shipment Logging -' . $purchaseOrderId. json_encode($shipmentInfos));
                $orderObj->setShipmentInfos($shipmentInfos);
                $orderObj->setLineItems($lineItem);

                $service = '';
                if (!is_bool($googleClient)) {
                    $this->logger('Shipment-' . $purchaseOrderId, 'Google Client ' . $googleClient->getClientId(),array(),'',false,'info');
                    $service = new \Google_Service_ShoppingContent($googleClient);
                }
                $response = $service->orders->shiplineitems($merchantId, $purchaseOrderId, $orderObj);
            }

        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Shipment-' . $purchaseOrderId, 'Purchase Order Id ' . $e->getMessage());
            }
            return false;

        }
        return $response;
    }

    public function fetchOrderFromGoogleExpressByOrderId($orderId)
    {
        try {
            $googleClient = $this->authClient();
            $account = $this->multiAccountHelper->getAccountRegistry();
            $merchantId = ($account) ? $account->getMerchantId() : $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
            $service = '';
            if (!is_bool($googleClient)) {
                $service = new \Google_Service_ShoppingContent($googleClient);
                $order = $service->orders->get($merchantId, $orderId);
                return $order;
            }
            return false;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Order id Fetch-' . $orderId, 'Purchase Order Id ' . $e->getMessage());
            }
            return false;
        }

    }

    public function cancelOrderOnGXpress($purchaseOrderId)
    {
        try {
            $googleClient = $this->authClient();
            $account = $this->multiAccountHelper->getAccountRegistry();
            $merchantId = ($account) ? $account->getMerchantId() : $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
            $service = '';
            if (!is_bool($googleClient)) {
                $service = new \Google_Service_ShoppingContent($googleClient);
                $cancelOrder = new \Google_Service_ShoppingContent_OrdersCancelRequest();
                $cancelOrder->setOperationId($purchaseOrderId . '0');
                $response = $service->orders->cancel($merchantId, $purchaseOrderId, $cancelOrder);
                return $response;
            }
            return false;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('Shipment-' . $purchaseOrderId, 'Purchase Order Id ' . $e->getMessage());
            }
            return false;
        }
    }

    public function refundOrder($purchaseOrderId, $orderData)
    {
        try {
            $googleClient = $this->authClient();
            $account = $this->multiAccountHelper->getAccountRegistry();
            $merchantId = ($account) ? $account->getMerchantId() : $this->scopeConfig(self::CONFIG_PATH_MERCHANT_ID);
            $service = '';
            if (!is_bool($googleClient)) {
                $service = new \Google_Service_ShoppingContent($googleClient);
                $refund = $service->orders->refund($merchantId);
                return $refund;
            }
            return false;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger('refund error', $e->getMessage());
            }
            return false;
        }

    }
}