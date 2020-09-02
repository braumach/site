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
 * @category    Ced
 * @package     Ced_TradeMe
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\TradeMe\Helper;
/**
 * Class Order
 * @package Ced\TradeMe\Helper
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\objectManagerInterface
     */
    public $_objectManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $_jdecode;
    /**
     * @var \Ced\TradeMe\Model\ResourceModel\Orders\CollectionFactory
     */
    public $_trademeOrder;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customerRepository;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $productRepository;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $_product;
    /**
     * @var Data
     */
    public $datahelper;
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    public $cartManagementInterface;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $cartRepositoryInterface;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;
    /**
     * @var \Ced\TradeMe\Helper\MultiAccount
     */
    protected $multiAccountHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public $countryData;

    /**
     * Order constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\objectManagerInterface $_objectManager
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Json\Helper\Data $_jdecode
     * @param \Ced\TradeMe\Model\ResourceModel\Orders\CollectionFactory $_trademeOrder
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Catalog\Model\ProductFactory $_product
     * @param Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\CountryFactory $countryData,
        \Magento\Framework\objectManagerInterface $_objectManager,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Json\Helper\Data $_jdecode,
        \Ced\TradeMe\Model\ResourceModel\Orders\CollectionFactory $_trademeOrder,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Catalog\Model\ProductFactory $_product,
        Logger $logger,
        Data $dataHelper,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Catalog\Helper\Product $productHelper
    )
    {
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        $this->orderService = $orderService;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->_objectManager = $_objectManager;
        $this->_storeManager = $storeManager;
        $this->quote = $quote;
        $this->countryData = $countryData;
        $this->quoteManagement = $quoteManagement;
        $this->_product = $product;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->_jdecode = $_jdecode;
        $this->customerFactory = $customerFactory;
        $this->_trademeOrder = $_trademeOrder;
        $this->_product = $_product;
        parent::__construct($context);
        $this->_coreRegistry = $registry;
        $this->datahelper = $dataHelper;
        $this->messageManager = $manager;
        $this->logger = $logger;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->currencyFactory = $currencyFactory;
        $this->productHelper = $productHelper;

    }

    public function fetchOrders($accountIds = [])
    {

        $orderFetchResult = array();
        foreach ($accountIds as $accountId) {
            if ($this->_coreRegistry->registry('trademe_account'))
                $this->_coreRegistry->unregister('trademe_account');
            $account = $this->multiAccountHelper->getAccountRegistry($accountId);
            $accountName = $account->getAccountCode();
            $this->datahelper->updateAccountVariable();
            $store_id = $account->getAccountStore();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $store = $this->_storeManager->getStore($store_id)/*->setCurrentCurrency($currency)*/;
            $response = $this->datahelper->getOrders();
            $count = 0;
            $orderArray = [];
            $found = '';
            $orders = [];
            try {
                if (isset($response['List'])) {
                    foreach ($response['List'] as $listItem) {
                        if (isset($listItem['TrackedParcels']) && empty($listItem['TrackedParcels'])) {
                            $orders[$listItem['PurchaseId']]['OrderId'] = $listItem['OrderId'];
                            $orders[$listItem['PurchaseId']]['SoldDate'] = $listItem['SoldDate'];
                            $orders[$listItem['PurchaseId']]['Buyer'] = $listItem['Buyer'];
                            $orders[$listItem['PurchaseId']]['DeliveryAddress'] = $listItem['DeliveryAddress'];
                            unset($listItem['Buyer']);
                            unset($listItem['DeliveryAddress']);
                            unset($listItem['SoldDate']);
                            $orders[$listItem['PurchaseId']]['items'][] = $listItem;
                        }

                        if (!isset($listItem['TrackedParcels'])) {
                            $orders[$listItem['PurchaseId']]['OrderId'] = $listItem['OrderId'];
                            $orders[$listItem['PurchaseId']]['SoldDate'] = $listItem['SoldDate'];

                            $orders[$listItem['PurchaseId']]['Buyer'] = $listItem['Buyer'];
                            $orders[$listItem['PurchaseId']]['DeliveryAddress'] = $listItem['DeliveryAddress'];
                            unset($listItem['Buyer']);
                            unset($listItem['DeliveryAddress']);
                            unset($listItem['SoldDate']);
                            $orders[$listItem['PurchaseId']]['items'][] = $listItem;
                        }
                    }
                }
                foreach ($orders as $purchaseId => $trademeOrder) {
                    $email = isset($trademeOrder['Buyer']['Email']) ? $trademeOrder['Buyer']['Email'] : 'customer'
                        .mt_rand(10,100).'@trademe.com';
                    $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->setWebsiteId($websiteId)->loadByEmail($email);
                    $purchaseOrderid = $trademeOrder['OrderId'];
                    $resultdata = $this->_trademeOrder->create()
                        ->addFieldToFilter('status', ['in' => ['shipped', 'acknowledged']])
                        ->addFieldToFilter('trademe_order_id', $trademeOrder['OrderId'])->getData();
                    if (empty($resultdata)) {
                        $ncustomer = $this->getCustomer($trademeOrder['DeliveryAddress'], $customer, $email);
                        if (!$ncustomer) {
                            return false;
                        } else {
                            $count++;
                            $this->generateQuote($store, $ncustomer, $trademeOrder);
                        }
                    }
                }
                if ($count > 0) {
                    $orderFetchResult['success'] = "You have " . $count . " orders from TradeMe for account " . $accountName;
                    $this->notificationSuccess($count);
                } else {
                    $orderFetchResult['error'] = 'No New Orders Found';
                }
            } catch (\Exception $e) {
                $orderFetchResult['error'] = "Order Import has some error : Please check activity Logs";
                $this->logger->addError('In Order Fetch: ' . $e->getMessage(), ['path' => __METHOD__]);
            }
        }
        return $orderFetchResult;
    }

    public function getCustomer($buyer, $customer, $email)
    {
        $customerGroupId = $this->scopeConfig->getValue('trademe_config/order/customer_group');
        $customerData = $customer->getData();
        if (empty($customerData)) {
            try {
                $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
                $websiteId = $this->_storeManager->getStore()->getWebsiteId();
                $customer->setWebsiteId($websiteId)
                    //->setStoreId($storeId)
                    ->setFirstname($buyer['Name'])
                    ->setLastname($buyer['Name'])
                    ->setEmail($email)
                    ->setPassword("password")
                    ->setGroupId($customerGroupId);
                $customer->save();
                return $customer;
            } catch (\Exception $e) {
                $this->logger->addError('In Create Customer: has exception '.$e->getMessage(), ['path' => __METHOD__]);
                return false;
            }
        }
        return $customer;
    }

    public function generateQuote($store, $ncustomer, $result)
    {
        $order_place = date("Y-m-d");
        try {
            $encodeOrderData = $this->_jdecode->jsonEncode($result);
            if ($this->_coreRegistry->registry('trademe_account'))
                $account = $this->_coreRegistry->registry('trademe_account');
            $accountId = isset($account) ? $account->getId() : '';
            $orderWithoutStock = $this->scopeConfig->getValue('trademe_config/order/prod_outofstock');
            $shipMethod = $this->scopeConfig->getValue('trademe_config/order/ship_method');
            $paymentMethod = $this->scopeConfig->getValue('trademe_config/order/pay_method');
            $shippingcost = '';
            $cart_id = $this->cartManagementInterface->createEmptyCart();
            $quote = $this->cartRepositoryInterface->get($cart_id);
            $quote->setStore($store);
            $quote->setCurrency();
            $customer = $this->customerRepository->getById($ncustomer->getId());
            $quote->assignCustomer($customer);
            $transArray = $result['items'];

            foreach ($transArray as $transaction) {
                $defaultSku = false;
                $product = false;
                $firstName = $result['DeliveryAddress']['Name'];
                $lastName = $result['DeliveryAddress']['Name'];
                $date = $result['SoldDate'];
                $matches = [];
                preg_match("/\/Date\((.*?)\)\//", $date, $matches);
                if (isset($matches[1])) {
                    $order_place = $matches[1];
                    $finalDate = date("Y-m-d H:i:s", $order_place);
                } else {
                    $order_place = date("Y-m-d H:i:s");
                }
               // $order_place = date("Y-m-d", strtotime($result['SoldDate']));
                $product = false;
                $sku = $transaction['SKU'];
                if (!empty($sku)) {
                    $product_obj = $this->_objectManager->get('Magento\Catalog\Model\Product');
                    $product = $product_obj->loadByAttribute('sku', $sku);
                }

                if ($product) {
                    $product = $this->_product->create()->load($product->getEntityId());

                    if ($product->getStatus() == '1') {
                        $stockRegistry = $this->_objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
                        /* Get stock item */
                        $stock = $stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                        $stockstatus = ($stock->getQty() > 0) ? ($stock->getIsInStock() == '1' ? ($stock->getQty() >= $transaction ['QuantitySold'] ? true : false) : false) : false;
                        $orderWithoutStock = $this->scopeConfig->getValue('trademe_config/order/order_out_of_stock');
                        if (!$stockstatus && $orderWithoutStock == 1) {
                            /*$stockRegistry = $this->_objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface');
                            $product_obj = $this->_objectManager->create('Magento\Catalog\Model\Product');
                            $product = $product_obj->loadByAttribute('sku', $sku);
                            $updateQty = $transaction ['QuantityPurchased'] + 1;
                            $stock = $stockRegistry->getStockItem($product->getId());
                            $stock->setIsInStock(1);
                            $stock->setQty(intval($updateQty));
                            $stock->save();
                            $product->save();*/
                            $quote->setIsSuperMode(true);
                            $this->productHelper->setSkipSaleableCheck(true);
                            $stockstatus = true;
                        }
                        if ($stockstatus) {
                            $productArray [] = [
                                'id' => $product->getEntityId(),
                                'qty' => $transaction ['QuantitySold']];
                            $price = $transaction['SalePrice'];
                            $currencyRate = $this->currencyFactory->create()->load($store->getBaseCurrency())->getAnyRate($store->getCurrentCurrency());
                            $qty = $transaction ['QuantitySold'];
                            $baseprice = $qty * $price;
                            $shippingcost = isset($transaction['ShippingPrice']) ? $transaction['ShippingPrice'] : 0;
                            $rowTotal = $price * $qty;
                            $product->setPrice($price)
                                ->setTierPrice([])
                                ->setBasePrice($baseprice)
                                ->setOriginalCustomPrice($price)
                                ->setRowTotal($rowTotal)
                                ->setBaseRowTotal($rowTotal);

                                $quote->addProduct($product, (int)$qty);
                        } else {
                            //$this->messageManager->addErrorMessage("No Inventory found for Product SKU: ".$product->getSku());
                            $this->rejectOrder($transaction , "No Inventory found for Product SKU: ".$product->getSku());
                        }
                    }
                }
            }
            if (isset($productArray)) {
                $firstname = $lastname = '';
                $lastArray = [];
                $lastname =  $lastName;
                $firstname = $firstName;
                $lastname = $lastName;
                $region = $result ['DeliveryAddress']['Country'];
                if (isset($result['DeliveryAddress'] ['Address2']) && !empty($result['DeliveryAddress'] ['Address2']) && is_string($result['DeliveryAddress'] ['Address2'])) {
                    $street = $result['DeliveryAddress'] ['Address1'].' '.$result['DeliveryAddress'] ['Address2'];
                } else {
                    $street = $result['DeliveryAddress'] ['Address1'];
                }
                $phone = 000;
                if (isset($result['DeliveryAddress']['PhoneNumber'])) {
                    if (is_array($result['DeliveryAddress']['PhoneNumber'])) {
                        $phone = implode(', ', $result['DeliveryAddress']['PhoneNumber']);
                        $phone = $phone ==  '' ? 0 : $phone;
                    }
                    if (is_string($result['DeliveryAddress']['PhoneNumber'])) {
                        $phone = $result['DeliveryAddress']['PhoneNumber'];
                    }
                }

                if (isset($result['DeliveryAddress']['Name'])) {
                    $name = explode(" ", $result['DeliveryAddress']['Name'], 2);
                    $name = $name ==  '' ? [] : $name;
                    $firstname = $name[0];
                    if (isset($name[1]) && $name[1] != 0){
                        $lastname = $name[1];
                    } else {
                        $lastname = $firstname;
                    }
                }

                $countriesData = $this->countryData->create()->getCollection();
                $countryId = 'NZ';
                foreach ($countriesData as $countryData) {
                    if (trim($result['DeliveryAddress']['Country']) == $countryData->getName()) {
                        $countryId = $countryData->getData('country_id');
                    }
                }
                $shipAdd = [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'street' => $street,
                    'city' => $result['DeliveryAddress']['City'],
                    'country_id' => $countryId ,
                    'region' => $region,
                    'postcode' => $result ['DeliveryAddress']['Postcode'],
                    'telephone' => $phone,
                    'fax' => '',
                    'save_in_address_book' => 1
                ];
                $billAdd = [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'street' => $street,
                    'city' => $result['DeliveryAddress']['City'],
                    'country_id' => $countryId ,
                    'region' => $region,
                    'postcode' => $result ['DeliveryAddress']['Postcode'],
                    'telephone' => $phone,
                    'fax' => '',
                    'save_in_address_book' => 1
                ];
                $orderData = [
                    'currency_id' => 'USD',
                    'email' => 'test@cedcommerce.com',
                    'shipping_address' => $shipAdd
                ];

                $quote->getBillingAddress()->addData($billAdd);
                $shippingAddress = $quote->getShippingAddress()->addData($shipAdd);
                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($shipMethod);
                $quote->setPaymentMethod($paymentMethod);
                $quote->setInventoryProcessed(false);
                $quote->save();
                $quote->getPayment()->importData([
                    'method' => $paymentMethod
                ]);
                $quote->collectTotals()->save();
                foreach ($quote->getAllItems() as $item) {
                    $item->setDiscountAmount(0);
                    $item->setBaseDiscountAmount(0);
                    $item->setOriginalCustomPrice($item->getPrice())
                        ->setOriginalPrice($item->getPrice())
                        ->save();
                }
                $order = $this->cartManagementInterface->submit($quote);
                $preFix = $this->scopeConfig->getValue('trademe_config/order/order_id_prefix');
                $orderId = $preFix.$order->getIncrementId();
                $order->setIncrementId($orderId)->setShippingAmount($shippingcost)->setBaseShippingAmount($shippingcost)->save();
                foreach ($order->getAllItems() as $item) {
                    $item->setOriginalPrice($item->getPrice())
                        ->setBaseOriginalPrice($item->getPrice())
                        ->save();
                }
                // after save order
                $orderData = [
                    'trademe_order_id' => $result['OrderId'],
                    'order_place_date' => $order_place,
                    'magento_order_id' => $order->getIncrementId(),
                    'status' => 'acknowledged',
                    'order_data' => $encodeOrderData,
                    'failed_order_reason' => "",
                    'account_id' => $accountId
                ];
                $trademeModel = $this->_objectManager->create('Ced\TradeMe\Model\Orders')->loadByField('trademe_order_id', $result['OrderId']);
                if ($trademeModel) {
                    $trademeModel->addData($orderData)->save();
                } else {
                    $this->_objectManager->create('Ced\TradeMe\Model\Orders')->addData($orderData)->save();
                }
                $this->sendMail($result['OrderId'], $order->getIncrementId(), $order_place);
                $this->generateInvoice($order);
                $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($order->getId());
                $order->save();
            } else {
                $this->rejectOrder($result, "No Product found for Order");
            }
        } catch (\Exception $e) {
            $this->rejectOrder($result, $e->getMessage());
            $this->logger->addError('In Generate Quote: '.$e->getMessage(), ['path' => __METHOD__]);
        } catch (\Error $e) {
            $this->rejectOrder($result, $e->getMessage());
            $this->logger->addError('In Generate Quote: '.$e->getMessage(), ['path' => __METHOD__]);
        }
    }

    public function rejectOrder($orderResponseData, $message) {
        $encodeOrderData = $this->_jdecode->jsonEncode($orderResponseData);
        if ($this->_coreRegistry->registry('trademe_account'))
            $account = $this->_coreRegistry->registry('trademe_account');
        $accountId = isset($account) ? $account->getId() : '';
        $orderData = [
            'trademe_order_id' => $orderResponseData['OrderId'],
            'order_place_date' => date("Y-m-d"),
            'magento_order_id' => '',
            'status' => 'failed',
            'order_data' => $encodeOrderData,
            'failed_order_reason' => $message,
            'account_id' => $accountId
        ];
        $trademeModel = $this->_objectManager->create('Ced\TradeMe\Model\Orders')->loadByField('trademe_order_id', $orderResponseData['OrderId']);
        if ($trademeModel) {
            $trademeModel->addData($orderData)->save();
        } else {
            $this->_objectManager->create('Ced\TradeMe\Model\Orders')->addData($orderData)->save();
        }
        $mageId = null;
        $placeDate = null;

    }

    public function sendMail($trademeOrderId, $mageOrderId = null, $placeDate = null)
    {
        try {
                $body = '<table cellpadding="0" cellspacing="0" border="0">
                <tr> <td> <table cellpadding="0" cellspacing="0" border="0">
                    <tr> <td class="email-heading">
                        <h1>You have a new order from Trade Me.</h1>
                        <p> Please review your admin panel."</p>
                    </td> </tr>
                </table> </td> </tr>
                <tr> 
                    <td>
                        <h4>Merchant Order Id' . $trademeOrderId . '</h4>
                    </td>
                    <td>
                        <h4>Magneto Order Id' . $mageOrderId . '</h4>
                    </td>
                    <td>
                        <h4>Order Place Date' . $placeDate . '</h4>
                    </td>
                </tr>  
            </table>';
            $to_email = $this->scopeConfig->getValue('trademe_config/order/order_notify_email');
            $to_name = 'Trade Me Seller';
            $subject = 'Imp: New Trade Me Order Imported';
            $senderEmail = 'trademeadmin@cedcommerce.com';
            $senderName = 'TradeMe';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: ' . $senderEmail . '' . "\r\n";
            mail($to_email, $subject, $body, $headers);
            return true;
        } catch (\Exception $e) {
            $this->logger->addError('In Send E-Mail: '.$e->getMessage(), ['path' => __METHOD__]);
        }
    }

    public function generateInvoice($order)
    {
        try {
            $invoice = $this->_objectManager->create(
                'Magento\Sales\Model\Service\InvoiceService')->prepareInvoice(
                $order);
            $invoice->register();
            $invoice->save();
            $transactionSave = $this->_objectManager->create(
                'Magento\Framework\DB\Transaction')->addObject(
                $invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
            $order->addStatusHistoryComment(__(
                'Notified customer about invoice #%1.'
                , $invoice->getId()))->setIsCustomerNotified(true)->save();
            $order->setStatus('processing')->save();
        } catch (\Exception $e) {
            $this->logger->addError('In Generate Invoice: '.$e->getMessage(), ['path' => __METHOD__]);
        }
    }

    public function notificationSuccess($count)
    {
        $model = $this->_objectManager->create('\Magento\AdminNotification\Model\Inbox');
        $date = date("Y-m-d H:i:s");
        $model->setData('severity', 4);
        $model->setData('date_added', $date);
        $model->setData('title', "New Trade Me Orders");
        $model->setData('description', "Congratulation !! You have received " . $count . " new orders for Trade Me");
        $model->setData('url', "#");
        $model->setData('is_read', 0);
        $model->setData('is_remove', 0);
        $model->save();
        return true;
    }

}