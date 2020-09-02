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
 * @package     Ced_GXpress
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Helper;

/**
 * Class Order
 * @package Ced\GXpress\Helper
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
     * @var \Ced\GXpress\Model\ResourceModel\Orders\CollectionFactory
     */
    public $_gxpressOrder;
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
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public $gxpressLib;

    /*
     * @var \Magento\Catalog\Helper\Product
     */
    public $productHelper;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $_indexerFactory;
    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $_indexerCollectionFactory;

    protected $_addressFactory;

    protected $stockItem;

    public $stock;

    /** @var \Magento\Directory\Model\RegionFactory */
    public $regionFactory;

    //public $USTaxedRegions = [];

    public $objectFactory;

    public $logger;
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
     * @param \Ced\GXpress\Model\ResourceModel\Orders\CollectionFactory $_gxpressOrder
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Catalog\Model\ProductFactory $_product
     * @param Data $dataHelper
     * @param GXpresslib $gxpressLib
     * @param \Magento\Catalog\Helper\Product $productHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\objectManagerInterface $_objectManager,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Json\Helper\Data $_jdecode,
        \Ced\GXpress\Model\ResourceModel\Orders\CollectionFactory $_gxpressOrder,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Catalog\Model\ProductFactory $_product,
        Logger $logger,
        Data $dataHelper,
        \Ced\GXpress\Helper\GXpresslib $gxpressLib,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
    )
    {
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        $this->orderService = $orderService;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->_objectManager = $_objectManager;
        $this->_storeManager = $storeManager;
        $this->quote = $quote;
        $this->regionFactory = $regionFactory;
        $this->quoteManagement = $quoteManagement;
        $this->_product = $product;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->_jdecode = $_jdecode;
        $this->customerFactory = $customerFactory;
        $this->_gxpressOrder = $_gxpressOrder;
        $this->_product = $_product;
        $this->objectFactory = $objectFactory;
        parent::__construct($context);
        $this->_coreRegistry = $registry;
        $this->datahelper = $dataHelper;
        $this->gxpressLib = $gxpressLib;
        $this->messageManager = $manager;
        $this->logger = $logger;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->productHelper = $productHelper;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
        $this->_addressFactory = $addressFactory;
        $this->stockItem = $stockItem;
    }

    // you can call this function to do reindexing
    public function reIndexing()
    {
        $indexerCollection = $this->_indexerCollectionFactory->create();
        $ids = $indexerCollection->getAllIds();
        foreach ($ids as $id) {
            $idx = $this->_indexerFactory->create()->load($id);
            $idx->reindexAll($id); // this reindexes all
            //$idx->reindexRow($id); // or you can use reindexRow according to your need
        }
    }

    /**
     * @param array $accountIds
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getNewOrders($accountIds = array(), $sync = null)
    {
        //$store_id = $this->scopeConfig->getValue('gxpress_config/gxpress_setting/storeid');
        $realCustomer = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/real_customer');
        $customerEmail = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/customer_email');
        $customerName = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/customer_name');
        $customerLastname = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/customer_lastname');
        $customerGroup = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/customer_group');
        $customerPassword = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/customer_password');

        $orderFetchResult = array();

        if (!is_array($accountIds)) {
            $accountIds = array($accountIds);
        }
        foreach ($accountIds as $accountId) {
            $hasError = false;
            if ($this->_coreRegistry->registry('gxpress_account'))
                $this->_coreRegistry->unregister('gxpress_account');
            $account = $this->multiAccountHelper->getAccountRegistry($accountId);
            $accountName = $account->getAccountCode();
            $this->datahelper->updateAccountVariable();
            $store_id = $account->getAccountStore();
            $websiteId = $this->_storeManager->getStore($store_id)->getWebsiteId();
            $store = $this->_storeManager->getStore($store_id);
            $this->_storeManager->setCurrentStore($store_id);

            if ($sync) {
                $response = $this->gxpressLib->fetchOrderFromGoogleExpressByOrderId($sync);
            } else {
                $response = $this->gxpressLib->fetchOrderFromGXpress();
            }

            if ($response == 'error' || $response == 'please fetch the token') {
                $orderFetchResult['error'] = $response;
                return $orderFetchResult;
            }

            $count = 0;
            $orderArray = [];
            $found = '';
            try {
                if (is_object($response) && get_class($response) == 'Google_Service_ShoppingContent_OrdersListResponse' && $response['resources'] >= 1) {
                    foreach ($response['resources'] as $order) {
                        $gxpressOrderid = $order['id'];
                        $orderObject = $order;
                        $email = $order['customer']['email'];
                        $cName = $order['customer']['fullName'];
                        $cName = explode(" ", $cName);
                        if (isset($cName) && !empty($cName[0])) {
                            $firstName = $cName[0];
                        }
                        unset($cName[0]);
                        $lastName = implode(" ", $cName);
                        $lastName = !empty($lastName) && ($lastName != '') ? $lastName : 'N/A';

                        if ($realCustomer == 0 && $customerEmail != '' && $customerName != '' && $customerLastname != '') {
                            $email = $customerEmail;
                            $firstName = $customerName;
                            $lastName = $customerLastname;
                        }

                        $customer = $this->_objectManager->get('Magento\Customer\Model\Customer')->setWebsiteId($websiteId)->setStoreId($store_id)->loadByEmail($email);

                        $resultdata = $this->_gxpressOrder->create()->addFieldToFilter('gxpress_order_id', $gxpressOrderid)->addFieldToFilter('status', ['neq' => ['failed']]);

                        if (!$this->validateString($resultdata->getData())) {
                            $ncustomer = $this->_assignCustomer($customer, $firstName, $lastName, $email, $websiteId, $order, $sync);
                            //$this->logger->addError('In Order Fetch: ' . 'customer id => '.$ncustomer->getId(), ['path' => __METHOD__]);
                            if (!$ncustomer) {
                                return false;
                            } else {
                                $count = $this->generateQuote($store, $ncustomer, $order, $count);
                            }
                        }
                    }
                    if ($count == 0) {
                        $orderFetchResult['error'] = 'No New Orders Found';
                    }
                    if ($hasError) {
                        $orderFetchResult['error'] = 'Failed to Fetch Some Order.. Please Check Failed Googleexpress Orders Import Log.';
                    }
                } else if (is_object($response) && get_class($response) == 'Google_Service_ShoppingContent_Order') {
                    $order = $response;
                    $gxpressOrderid = $order['id'];
                    $orderObject = $order;
                    $email = $order['customer']['email'];
                    $cName = $order['customer']['fullName'];
                    $cName = explode(" ", $cName);
                    if (isset($cName) && !empty($cName[0])) {
                        $firstName = $cName[0];
                    }
                    unset($cName[0]);
                    $lastName = implode(" ", $cName);
                    $lastName = !empty($lastName) && ($lastName != '') ? $lastName : 'N/A';

                    if ($realCustomer == 0 && $customerEmail != '' && $customerName != '' && $customerLastname != '') {
                        $email = $customerEmail;
                        $firstName = $customerName;
                        $lastName = $customerLastname;
                    }

                    $customer = $this->_objectManager->get('Magento\Customer\Model\Customer')->setWebsiteId($websiteId)->loadByEmail($email);
                    $resultdata = $this->_gxpressOrder->create()->addFieldToFilter('gxpress_order_id', $gxpressOrderid)->addFieldToFilter('status', ['in' => ['shipped', 'delivered']]);

                    if (!$this->validateString($resultdata->getData())) {
                        $ncustomer = $this->_assignCustomer($customer, $firstName, $lastName, $email, $websiteId, $order);
                        if (!$ncustomer) {
                            return false;
                        } else {
                            $count = $this->generateQuote($store, $ncustomer, $order, $count, $sync);
                        }
                    }
                    if ($count == 0) {
                        $orderFetchResult['error'] = 'No New Orders Found';
                    }
                    if ($hasError) {
                        $orderFetchResult['error'] = 'Failed to Fetch Some Order.. Please Check Failed Googleexpress Orders Import Log.';
                    }

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

    /**
     * @param $customer
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $websiteId
     * @param $result
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _assignCustomer($customer, $firstName, $lastName, $email, $websiteId, $result, $sync = null)
    {
        $order_place = date("Y-m-d");

        $realCustomer = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/real_customer');
        /*$customerEmail = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/customer_email');
        $customerName = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/customer_name');
        $customerLastname = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/customer_lastname');*/
        $customerGroup = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/customer_group');
        $customerPassword = $this->scopeConfig->getValue('gxpress_config/gxpress_order/gxpress_customer/customer_password');

        if (!$this->validateString($customer->getId())) {
            if ($realCustomer == 0) {
                $password = $customerPassword;
                $groupId = $customerGroup;
            } else {
                $password = "password";
                $groupId = 1;
            }
            if ($groupId == null) {
                $groupId = 1;
            }
            try {
                $websiteId = $this->_storeManager->getStore()->getWebsiteId();
                $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
                $customer->setWebsiteId($websiteId);
                $customer->setEmail($email);
                $customer->setFirstname($firstName);
                $customer->setLastname($lastName);
                $customer->setPassword($password);
                $customer->setGroupId($groupId);
                $customer->save();
                return $customer;
            } catch (\Exception $e) {
                $encodeOrderData = $this->_jdecode->jsonEncode($result);
                $orderData = [
                    'gxpress_order_id' => $result['id'],
                    /*'gxpress_record_no' => json_encode($result['lineItems']),*/
                    'order_place_date' => $order_place,
                    'magento_id' => '',
                    'magento_order_id' => '',
                    'status' => 'failed',
                    'order_data' => $encodeOrderData,
                    'failed_order_reason' => $e->getMessage()
                ];
                $gxpressModel = $this->_objectManager->create('Ced\GXpress\Model\Orders')->loadByField('gxpress_order_id', $result['id']);
                if ($gxpressModel) {
                    $gxpressModel->addData($orderData)->save();
                } else {
                    $this->_objectManager->create('Ced\GXpress\Model\Orders')->addData($orderData)->save();
                }
                $this->logger->addError('In Create Customer: has exception ' . $e->getMessage(), ['path' => __METHOD__]);
                $this->messageManager->addErrorMessage($e->getMessage());
                return false;
            }
        } else {
            $nCustomer = $this->customerRepository->getById($customer->getId());
            return $nCustomer;
        }
    }

    /**
     * @param $store
     * @param $ncustomer
     * @param $result
     * @param $count
     * @return int
     */
    public function generateQuote($store, $ncustomer, $result, $count, $sync = null)
    {
        $order_place = date("Y-m-d");
        $total = 0;

        try {
//            $this->processTaxRegions();
            $encodeOrderData = $this->_jdecode->jsonEncode($result);
            if ($this->_coreRegistry->registry('gxpress_account'))
                $account = $this->_coreRegistry->registry('gxpress_account');
            $accountId = isset($account) ? $account->getId() : '';
            $orderWithoutStock = $this->scopeConfig->getValue('gxpress_config/gxpress_order/global_setting/order_on_out_of_stock');
            $shipMethod = $this->scopeConfig->getValue('gxpress_config/gxpress_order/global_setting/ship_method');
            $createProduct = $this->scopeConfig->getValue('gxpress_config/gxpress_order/global_setting/create_product');
            $shippingcost = '';
            $cart_id = $this->cartManagementInterface->createEmptyCart();
            $quote = $this->cartRepositoryInterface->get($cart_id);
            $quote->setStore($store);
            $quote->setCurrency();
            $customer = $this->customerRepository->getById($ncustomer->getId());
            $quote->assignCustomer($customer);
            $quote->setCustomerTaxClassId(0);

            $transactions = $result['lineItems'];
            if (isset($transactions[0])) {
                $transArray = $transactions;
            } else {
                $transArray[] = $transactions;
            }
            $grandTotal = 0;
            $taxes = 0;
            foreach ($transArray as $transaction) {
                //$firstName = $customer->getFirstName();
                //$lastName = $customer->getLastName();
                $order_place = date("Y-m-d", strtotime($result['placedDate']));
                $sku = isset($transaction['product']['offerId']) ? $transaction['product']['offerId'] : '';
                if (empty($sku)) {
                    $sku = $transaction['product']['id'];
                    $sku = explode(":", $sku);
                    $sku = $sku[3];
                }

                $product_obj = $this->_objectManager->get('Magento\Catalog\Model\Product');
                $product = $product_obj->loadByAttribute('sku', $sku);
                $tmpSku = $sku;
                if (!$product) {
                    $sku = substr_replace($sku, "", -2);
                    $product = $product_obj->loadByAttribute('sku', $sku);
                }

                if (!$product && $createProduct) {
                    $sku = $tmpSku;
                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
                    $product->setName($transaction['product']['title']);
                    $product->setTypeId('simple');
                    $product->setAttributeSetId(4);
                    $product->setSku($sku);
                    $product->setWebsiteIds(array(1));
                    $product->setVisibility(4);
                    $product->setUrlKey($sku);
                    $product->setStatus(true);
                    $product->setPrice($transaction['product']['price']['value']);
                    $product->setStockData(array(
                            'manage_stock' => 1, //manage stock
                            'is_in_stock' => 1, //Stock Availability
                            'qty' => $transaction ['quantityOrdered'] //qty
                        )
                    );
                    $product->getResource()->save($product);
                    $this->reIndexing();
                    $product = $product_obj->loadByAttribute('sku', $sku);
                    $this->productHelper->setSkipSaleableCheck(true);
                }
                $stockstatus = false;
                if ($product) {
                    $productArray = array();
                    $product = $this->_product->create()->load($product->getEntityId());
                    if ($product->getStatus() == true) {
                        $stock = $product->getData('quantity_and_stock_status');
                        if (is_array($stock)) {
                            $stock = isset($stock['qty']) ? $stock['qty'] : 0;
                        }

                        /* Get stock item */
                        $stockstatus = ($stock > 0) ? ($stock >= $transaction ['quantityOrdered'] ? true : false) : false;
                        if (!$stockstatus && $orderWithoutStock == 1) {
                            $productArray [] = [
                                'id' => $product->getEntityId(),
                                'qty' => $transaction ['quantityOrdered']];
                            $stockRegistry = $this->_objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface');
                            $product_obj = $this->_objectManager->create('Magento\Catalog\Model\Product');
                            $product = $product_obj->loadByAttribute('sku', $sku);
                            $updateQty = $transaction ['quantityOrdered'] + 1;
                            $stock = $stockRegistry->getStockItem($product->getId());
                            $stock->setIsInStock(1);
                            $stock->setQty(intval($updateQty));
                            $stock->save();
                            $product->save();
                            $stockstatus = true;
//                            $this->reIndexing();
                        }

                        if ($stockstatus) {
                            $productArray [] = [
                                'id' => $product->getEntityId(),
                                'qty' => $transaction ['quantityOrdered']];
                            $price = $transaction['product']['price']['value'];
                            $qty = $transaction ['quantityOrdered'];
                            $baseprice = $qty * $price;
                            $shippingcost = $result ['shippingCost']['value'];
                            $shippingTax = $result ['shippingCostTax']['value'];
                            $rowTotal = $price * $qty;
                            $tax[$sku] = $transaction['tax']['value'];
                            $rowTotal += $tax[$sku];
                            $taxes += $tax[$sku];
                            $grandTotal += $rowTotal;
                            $product->setTaxClassId($this->getTaxClassId())
                                ->setPrice($price)
                                ->setSpecialPrice($price)
                                ->setTierPrice([])
                                ->setBasePrice($baseprice)
                                ->setOriginalCustomPrice($price)
                                ->setRowTotal($rowTotal)
                                ->setBaseRowTotal($rowTotal);
                            $quote->setIsSuperMode(true);
                            $request = $this->objectFactory->create(['qty' => (int)$qty]);
                            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                                $collection = $product->getTypeInstance(true)
                                    ->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);
                                $bundleOptions = [];
                                $bundleOptionsQty = [];
                                /** @var $option \Magento\Bundle\Model\Option */
                                foreach ($collection as $option) {
                                    // foreach ($option->getProductLinks() as $selection) {
                                    /**
                                     * @var \Magento\Bundle\Api\Data\LinkInterface $selection
                                     */
                                    $bundleOptions[$option->getOptionId()][] = $option->getSelectionId();
                                    $bundleOptionsQty[$option->getOptionId()][] = $option->getSelectionQty();
                                    // }
                                }
                                $request->addData(
                                    [
                                        'qty' => (int)$qty,
                                        'bundle_option' => $bundleOptions,
                                        'bundle_option_qty' => $bundleOptionsQty
                                    ]
                                );
                                $product->setSkipCheckRequiredOption(true);
                            }
                            $quote->addProduct($product, $request);
                        } else {
                            $orderData = [
                                'gxpress_order_id' => $result['id'],
                                'order_place_date' => $order_place,
                                'magento_id' => '',
                                'magento_order_id' => '',
                                'status' => 'failed',
                                'failed_order_reason' => "No Inventory found for Product SKU: " . $product->getSku(),
                                'order_data' => $encodeOrderData,
                                'account_id' => $accountId
                            ];
                            $gxpressModel = $this->_objectManager->create('Ced\GXpress\Model\Orders')->loadByField('gxpress_order_id', $result['id']);
                            if ($gxpressModel) {
                                $gxpressModel->addData($orderData)->save();
                            } else {
                                $this->_objectManager->create('Ced\GXpress\Model\Orders')->addData($orderData)->save();
                            }
                            continue;
                        }
                    }
                }
            }
            if (isset($productArray) && $stockstatus) {
                $nameArray = explode(' ', $result['deliveryDetails']['address']['fullAddress'][0]);
                $firstname = $lastname = '';
                $lastArray = [];
                foreach ($nameArray as $value) {
                    if ($value != '') {
                        if ($firstname == '') {
                            $firstname = $value;
                        } else {
                            $lastArray[] = $value;
                        }
                    }
                }
                $lastname = implode(' ', $lastArray);
                $firstname = $firstname == '' ? "NA" : $firstname;
                $lastname = $lastname == '' ? "NA" : $lastname;
                $street = "";
                $region = is_array($result['deliveryDetails']['address']['region']) ? '' : $result['deliveryDetails']['address']['region'];
                if (isset($result['deliveryDetails']['address']['streetAddress'])
                    && !empty($result['deliveryDetails']['address']['streetAddress'])
                    && is_string($result['deliveryDetails']['address']['streetAddress'])) {
                    $street = $result['deliveryDetails']['address']['streetAddress'];
                } else {
                    if (isset($result['deliveryDetails']['address']['streetAddress'])
                        && !empty($result['deliveryDetails']['address']['streetAddress'])
                        && is_array($result['deliveryDetails']['address']['streetAddress'])) {
                        foreach ($result['deliveryDetails']['address']['streetAddress'] as $key => $addr) {
                            $street .= $result['deliveryDetails']['address']['streetAddress'][$key];
                        }
                    }
                }
                $phone = 000;
                if (isset($result['deliveryDetails']['phoneNumber'])) {
                    if (is_array($result['deliveryDetails']['phoneNumber'])) {
                        $phone = implode(', ', $result['deliveryDetails']['phoneNumber']);
                        $phone = $phone == '' ? 0 : $phone;
                    }
                    if (is_string($result['deliveryDetails']['phoneNumber'])) {
                        $phone = $result['deliveryDetails']['phoneNumber'];
                    }
                }
                $shipAdd = [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'street' => $street,
                    'city' => isset($result['deliveryDetails']['address']['locality']) ? $result['deliveryDetails']['address']['locality'] : '',
                    'country_id' => isset($result['deliveryDetails']['address']['country']) ? $result['deliveryDetails']['address']['country'] : '',
                    'region' => $region,
                    'postcode' => $result ['deliveryDetails']['address']['postalCode'],
                    'telephone' => $phone,
                    'fax' => '',
                    'save_in_address_book' => 1
                ];

                $orderData = [
                    'currency_id' => 'USD',
                    'email' => 'test@cedcommerce.com',
                    'shipping_address' => $shipAdd
                ];

                $billingAddressId = $customer->getDefaultBilling();
                $billingAddress = $this->_addressFactory->create()->load($billingAddressId);
                if($billingAddressId) {
                    $quote->getBillingAddress()->addData($billingAddress->getData());
                } else {
                    $quote->getBillingAddress()->addData($shipAdd);
                }
                $shippingAddress = $quote->getShippingAddress()->addData($shipAdd);
                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($shipMethod);
                $quote->setPaymentMethod('paybygxpress');
                $quote->setInventoryProcessed(false);
                $quote->getPayment()->importData([
                    'method' => 'paybygxpress'
                ]);


                foreach ($quote->getAllItems() as $item) {
                    $item->setDiscountAmount(0);
                    $item->setBaseDiscountAmount(0);
                    $item->setOriginalCustomPrice($item->getPrice());
                    $item->setOriginalPrice($item->getPrice());
                    $sku = $item->getSku();
                    if (isset($tax[$sku]) && $tax[$sku] > 0) {
                    /*if (isset($shipAdd['country_id']) && $shipAdd['country_id'] == 'US') {
                        if (isset($region) && in_array($region, $this->USTaxedRegions)) {
                            $item->setTaxAmount($tax[$sku]);
                            $percentage = number_format(($tax[$sku] / $item->getPrice() * 100), 2);
                            $item->setTaxPercent($percentage);
                        } else {
                            $item->setTaxAmount(0);
                            $item->setTaxPercent(0);
                        }
                    } else {*/
                        $item->setTaxAmount($tax[$sku]);
                        $percentage = number_format(($tax[$sku] / $item->getPrice() * 100), 2);
                        $item->setTaxPercent($percentage);
                   // }
                }
                    $item->save();
                }
                $quote->collectTotals()->save();

                $order = $this->cartManagementInterface->submit($quote);

                if ($order) {
                    $preFix = $this->scopeConfig->getValue('gxpress_config/gxpress_order/global_setting/order_id_prefix');
                    $orderId = $preFix . $order->getIncrementId();

                    if (isset($shipAdd['country_id']) && $shipAdd['country_id'] == 'US') {
                        /*if (isset($region) && in_array($region, $this->USTaxedRegions)) {
                            $order->setIncrementId($orderId)->setShippingAmount($shippingcost)->setBaseShippingAmount($shippingcost)
                                ->setShippingTaxAmount($shippingTax)->setBaseShippingTaxAmount($shippingTax)
                                ->setTaxAmount($taxes + $shippingTax)->setBaseTaxAmount($taxes + $shippingTax)
                                ->setBaseGrandTotal($shippingcost + $grandTotal + $shippingTax)
                                ->setGrandTotal($shippingcost + $grandTotal + $shippingTax)->save();
                        } else {*/
                            $order->setIncrementId($orderId)->setShippingAmount($shippingcost)->setBaseShippingAmount($shippingcost)
                                ->setShippingTaxAmount(0)->setBaseShippingTaxAmount(0)
                                ->setTaxAmount(0)->setBaseTaxAmount(0)
                                ->setBaseGrandTotal($shippingcost + $grandTotal -$taxes)
                                ->setGrandTotal($shippingcost + $grandTotal - $taxes)->save();
                       // }
                    } else {
                        $order->setIncrementId($orderId)->setShippingAmount($shippingcost)->setBaseShippingAmount($shippingcost)
                            ->setShippingTaxAmount($shippingTax)->setBaseShippingTaxAmount($shippingTax)
                            ->setTaxAmount($taxes + $shippingTax)->setBaseTaxAmount($taxes + $shippingTax)
                            ->setBaseGrandTotal($shippingcost + $grandTotal + $shippingTax)
                            ->setGrandTotal($shippingcost + $grandTotal + $shippingTax)
                            ->save();
                    }

                    $count = isset($order) ? $count + 1 : $count;
                    foreach ($order->getAllItems() as $item) {
                        $item->setOriginalPrice($item->getPrice())
                            ->setBaseOriginalPrice($item->getPrice())
                            ->save();
                    }
                    // after save order

                    $orderData = [
                        'gxpress_order_id' => $result['id'],
                        'order_place_date' => $order_place,
                        'magento_id' => $order->getId(),
                        'magento_order_id' => $order->getIncrementId(),
                        'status' => isset($result['status']) ? $result['status'] : 'failed',
                        'order_data' => $encodeOrderData,
                        'failed_order_reason' => "",
                        'account_id' => $accountId
                    ];

                    if ($orderData['status'] == "shipped") {
                        $orderData['shipment_data'] = json_encode($result->getShipments());
                    }

                    $gxpressModel = $this->_objectManager->create('Ced\GXpress\Model\Orders')->loadByField('gxpress_order_id', $result['id']);

                    if ($gxpressModel) {
                        $gxpressModel->addData($orderData)->save();
                    } else {
                        $this->_objectManager->create('Ced\GXpress\Model\Orders')->addData($orderData)->save();
                    }

                    $this->sendMail($result['id'], $order->getIncrementId(), $order_place);

                    $this->generateInvoice($order);

                    //$orderMsg = isset($result['BuyerCheckoutMessage']) ? $result['BuyerCheckoutMessage'] : '';
                    $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($order->getId());
                    //$order->addStatusHistoryComment($orderMsg);
                    $order->save();
                } else {
                    throw new \Exception('Failed to create order in Magento.');
                }
            } else if (!isset($productArray)) {
                $sku = $tmpSku;
                $orderData = [
                    'gxpress_order_id' => $result['id'],
                    /*'gxpress_record_no' => json_encode($result['lineItems']),*/
                    'order_place_date' => $order_place,
                    'magento_id' => '',
                    'magento_order_id' => '',
                    'status' => 'failed',
                    'failed_order_reason' => "No Product found for Order: " . $sku,
                    'order_data' => $encodeOrderData,
                    'account_id' => $accountId
                ];
                $gxpressModel = $this->_objectManager->create('Ced\GXpress\Model\Orders')->loadByField('gxpress_order_id', $result['id']);
                if ($gxpressModel) {
                    $gxpressModel->addData($orderData)->save();
                } else {
                    $this->_objectManager->create('Ced\GXpress\Model\Orders')->addData($orderData)->save();
                }
            }
            return $count;
        } catch (\Exception $e) {
            $encodeOrderData = $this->_jdecode->jsonEncode($result);
            if ($this->_coreRegistry->registry('gxpress_account'))
                $account = $this->_coreRegistry->registry('gxpress_account');
            $accountId = isset($account) ? $account->getId() : '';
            $orderData = [
                'gxpress_order_id' => $result['id'],
                /*'gxpress_record_no' => json_encode($result['lineItems']),*/
                'order_place_date' => $order_place,
                'magento_id' => '',
                'magento_order_id' => '',
                'status' => 'failed',
                'failed_order_reason' => $e->getMessage(),
                'order_data' => $encodeOrderData,
                'account_id' => $accountId
            ];
            $gxpressModel = $this->_objectManager->create('Ced\GXpress\Model\Orders')->loadByField('gxpress_order_id', $result['id']);
            if ($gxpressModel) {
                $gxpressModel->addData($orderData)->save();
            } else {
                $this->_objectManager->create('Ced\GXpress\Model\Orders')->addData($orderData)->save();
            }
            $this->logger->addError('In Generate Quote: ' . $e->getMessage(), ['path' => __METHOD__]);
        }
    }

//     private function processTaxRegions()
//     {
//         $regions = $this->regionFactory->create()
//             ->getCollection()
//             ->addFieldToSelect(['country_id', 'code'])
//             ->addFieldToFilter('country_id', ['eq' => 'US'])
//             ->addFieldToFilter('code', ['in' => ['FL', 'NC', 'GA']]);

//         /** @var \Magento\Directory\Model\Region $region */
//         foreach ($regions as $region) {
// //            $this->USTaxedRegions[$region->getData('code')] = $region->getId();
//             $this->USTaxedRegions[$region->getId()] = $region->getData('code');
//         }
//     }

    private function getTaxClassId()
    {
        return 0;
    }

    /**
     * @param $order
     */
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
            $this->logger->addError('In Generate Invoice: ' . $e->getMessage(), ['path' => __METHOD__]);
        }
    }

    /**
     * @param $order
     * @param $cancelleditems
     */
    public function generateShipment($order, $cancelleditems)
    {
        try {
            $shipment = $this->_prepareShipment($order, $cancelleditems);
            if ($shipment) {
                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);
                try {
                    $transactionSave = $this->_objectManager->create(
                        'Magento\Framework\DB\Transaction')->addObject(
                        $shipment)->addObject($shipment->getOrder());
                    $transactionSave->save();
                    $order->setStatus('complete')->save();
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        'Error in saving shipping:'
                        . $e);
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError('In Generate Shipment: ' . $e->getMessage(), ['path' => __METHOD__]);
        }
    }

    /**
     * @param $order
     * @param $cancelleditems
     * @return bool
     */
    public function _prepareShipment($order, $cancelleditems)
    {
        try {
            $shipment = $this->_objectManager->get(
                'Magento\Sales\Model\Order\ShipmentFactory')->create($order, isset($cancelleditems) ? $cancelleditems : [], []);
            if (!$shipment->getTotalQty()) {
                return false;
            }
            return $shipment;
        } catch (\Exception $e) {
            $this->logger->addError('In Prepare Shipment: ' . $e->getMessage(), ['path' => __METHOD__]);
        }
    }

    /**
     * @param $order
     * @param $cancelleditems
     */

    public function generateCreditMemo($order, $cancelleditems)
    {
        try {
            foreach ($order->getAllItems() as $orderItems) {
                $items_id = $orderItems->getId();
                $order_id = $orderItems->getOrderId();
            }
            $creditmemoLoader = $this->creditmemoLoaderFactory->create();
            $creditmemoLoader->setOrderId($order_id);
            foreach ($cancelleditems as $item_id => $cancelQty) {
                $creditmemo[$item_id] = ['qty' => $cancelQty];
            }
            $items = ['items' => $creditmemo,
                'do_offline' => '1',
                'comment_text' => 'GXpress Cancelled Orders',
                'adjustment_positive' => '0',
                'adjustment_negative' => '0'];
            $creditmemoLoader->setCreditmemo($items);
            $creditmemo = $creditmemoLoader->load();
            $creditmemoManagement = $this->_objectManager->create(
                'Magento\Sales\Api\CreditmemoManagementInterface'
            );
            if ($creditmemo) {
                $creditmemo->setOfflineRequested(true);
                $creditmemoManagement->refund($creditmemo, true);
            }
        } catch (\Exception $e) {
            $this->logger->addError('In Generate Credit Memo: ' . $e->getMessage(), ['path' => __METHOD__]);
        }
    }

    /**
     * @param $string
     * @return bool
     */
    public function validateString($string)
    {
        $stringValidation = (isset($string) && !empty($string)) ? true : false;
        return $stringValidation;
    }

    /**
     * @param $gxpressOrderId
     * @param $mageOrderId
     * @param $placeDate
     * @return void
     */
    public
    function sendMail($gxpressOrderId, $mageOrderId, $placeDate)
    {
        try {
            $body = '<table cellpadding="0" cellspacing="0" border="0">
                <tr> <td> <table cellpadding="0" cellspacing="0" border="0">
                    <tr> <td class="email-heading">
                        <h1>You have a new order from GXpress.</h1>
                        <p> Please review your admin panel."</p>
                    </td> </tr>
                </table> </td> </tr>
                <tr> 
                    <td>
                        <h4>Merchant Order Id' . $gxpressOrderId . '</h4>
                    </td>
                    <td>
                        <h4>Magneto Order Id' . $mageOrderId . '</h4>
                    </td>
                    <td>
                        <h4>Order Place Date' . $placeDate . '</h4>
                    </td>
                </tr>  
            </table>';
            $to_email = $this->scopeConfig->getValue('gxpress_config/gxpress_order/global_setting/order_notify_email');
            $to_name = 'GXpress Seller';
            $subject = 'Imp: New GXpress Order Imported';
            $senderEmail = 'gxpressadmin@cedcommerce.com';
            $senderName = 'GXpress';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: ' . $senderEmail . '' . "\r\n";
            mail($to_email, $subject, $body, $headers);
            return true;
        } catch (\Exception $e) {
            $this->logger->addError('In Send E-Mail: ' . $e->getMessage(), ['path' => __METHOD__]);
        }
    }

    /**
     * @param $count
     * @return void
     */
    public
    function notificationSuccess($count)
    {
        $model = $this->_objectManager->create('\Magento\AdminNotification\Model\Inbox');
        $date = date("Y-m-d H:i:s");
        $model->setData('severity', 4);
        $model->setData('date_added', $date);
        $model->setData('title', "New gxpress Orders");
        $model->setData('description', "Congratulation !! You have received " . $count . " new orders for gxpress");
        $model->setData('url', "#");
        $model->setData('is_read', 0);
        $model->setData('is_remove', 0);
        $model->save();
        return true;
    }
}
