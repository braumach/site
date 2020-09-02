<?php 

namespace Onesaas\Connect\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Api\StockStateInterface;

class Index extends Action
{
	private $pageNo;
	private $pageSize;
	private $lstUpdTm;
	private $ordCreatedTm;
	private $xml = '';
	
	protected $_appConfigScopeConfigInterface;
	protected $_paymentModelConfig;
	protected $_shippingConfig;
	protected $_stockStateInterface;
	protected $_stockRegistry;
	protected $_shipmentInterface;
	protected $_shipmentFactory;
	
	public function __construct(
	\Magento\Framework\App\Action\Context $context,
	\Magento\Framework\App\Config\ScopeConfigInterface $appConfigScopeConfigInterface,
	\Magento\Payment\Model\Config $paymentModelConfig,
	\Magento\Shipping\Model\Config $shippingConfig,
	\Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface,
	\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
	\Magento\Sales\Api\Data\ShipmentInterface $shipmentInterface,
	\Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory	
	)
	{
	parent::__construct($context);
	$this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
	$this->_paymentModelConfig = $paymentModelConfig;
	$this->_shippingConfig = $shippingConfig;
	$this->_stockStateInterface = $stockStateInterface;
	$this->_stockRegistry = $stockRegistry;
	$this->_shipmentInterface = $shipmentInterface;
	$this->_shipmentFactory = $shipmentFactory;	
	}
	
	public function execute()
	{		
		// Initialize XML Response
		header("Content-type:application/xml");
		$this->xml = '<?xml version="1.0" encoding="utf-8"?>';
		$this->xml .= '<OneSaas version="'.$this->getOneSaasConnectVersion().'" >';
		
		// Verify AccessKey
		if($this->verifyAccessKey($_GET['AccessKey'])) {

			// Parse parameters and initiliase variables
			$OrderCreatedTime = (isset($_GET['OrderCreatedTime']) && (strtotime($_GET['OrderCreatedTime'])>0)) ? Date('Y-m-d H:i:sP', strtotime($_GET['OrderCreatedTime'].'UCT')) : '1970-01-19T00:00:00+00:00';
			$LastUpdatedTime = (isset($_GET['LastUpdatedTime']) && (strtotime($_GET['LastUpdatedTime'])>0)) ? Date('Y-m-d H:i:sP', strtotime($_GET['LastUpdatedTime'].'UCT')) : '1970-01-19T00:00:00+00:00';
			$LUT = explode('T',max(trim($LastUpdatedTime), trim($OrderCreatedTime)));
			$OCT = explode('T',trim($OrderCreatedTime));
			$this->pageSize = (isset($_GET['PageSize']) && (is_numeric($_GET['PageSize']))) ? (int) $_GET['PageSize'] : 50;  // Allow override of PageSize from URL
			$this->lstUpdTm = implode(" ",$LUT);
			$this->ordCreatedTm = implode(" ",$OCT);
			$this->pageNo = (((isset($_GET['Page']) && (is_numeric($_GET['Page']))) ? (int) $_GET['Page'] : 0)) + 1;	// OS uses 0 for first page

			//Check action
			$action = (isset($_GET['Action']) ? $_GET['Action'] : '');
			$requestType = $_SERVER['REQUEST_METHOD'];

			switch ($action) {
				case 'Contacts':
					$this->contactsAction();
					break;
				case 'Products':
					$this->productsAction();
					break;
				case 'Orders':
					$this->ordersAction();
					break;					
				case 'Settings':
					$this->configurationsAction();
					break;
				case 'UpdateStock':
					$this->updateStockAction();
					break;
				case 'ShippingTracking':
					$this->shippingtrackingAction();
					break;
				default:
					$this->xml .= "<Error>Invalid Action</Error>";
			}
		} else {
			$this->xml .= "<Error>Invalid API Key</Error>";
		}		
		
		$this->xml .= '</OneSaas>';
		echo $this->xml;
	}
	
	/*** Product API Functions Start ***/

	public function productsAction(){
		if(!$this->getPageNoIsValid('Product')) {
			$this->xml .= "";
		} else {
			$this->xml .= $this->getProducts();
		}
	}
	
	private function getProductModel(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();     
		return $objectManager->create('Magento\Catalog\Model\Product');
	}

	private function getProductCollection($pageSize,$pageNo,$lstUpdTm){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
		$prod_data = $productCollection->create()
					->addFieldToFilter('updated_at', array('gt' => $lstUpdTm))
					->setPageSize($pageSize)
					->setCurPage($pageNo)
					->getData();
		return $prod_data;
	}	
	
	private function getProducts() {
		$content = '';
			$prodCol = $this->getProductCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
			foreach($prodCol as $prod){
				$content .= $this->getProductInfo($prod['entity_id'],$prod['updated_at']);
		}	
		return $content;
	}
	
	private function getProductInfo($id,$LUD){
		$prod = $this->getProductModel()->load($id);
		$type = $prod->getTypeId();
		if($prod->getStatus() == 1)
			$prodStatus = "True";
		else
			$prodStatus = "False";
		$stockItem = $this->_stockRegistry->getStockItem($prod->getEntityId());
		$manageStock = $stockItem->getManageStock();
		if($manageStock == 1)
			$isInventoried = "True";
		else
			$isInventoried = "False";
		$prod_info = array(
							'Code' => htmlspecialchars($prod->getSku()),
							'Name' => htmlspecialchars($prod->getName()),
							'Description'=>htmlspecialchars(strip_tags($prod->getDescription())),
							'IsActive'=>$prodStatus,
							'PublicUrl'=>htmlspecialchars($prod->getProductUrl()),
							'SalePrice'=>htmlspecialchars($prod->getPrice()),
							'CostPrice'=>htmlspecialchars($prod->getCost()),
							'IsInventoried'=>$isInventoried,
							'Type'=>($type=='simple')?'Product':$type
						);
   					
		return $this->getInfoXml('Product',$id,$LUD,$prod_info);
	}

	/*** Product API Functions End ***/		

	/*** Customer API Functions Start ***/

	public function contactsAction(){
		if(!$this->getPageNoIsValid('Contact')) {
			$this->xml .= "";
		} else {
			$this->xml .= $this->getCustomers();
		}
	}

	private function getCustomerModel(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		return $objectManager->create('Magento\Customer\Model\Customer');
	}

	private function getCustomerCollection($pageSize,$pageNo,$lstUpdTm){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$customerCollection = $objectManager->create('Magento\Customer\Model\ResourceModel\Customer\CollectionFactory');
			$cust_data = $customerCollection->create()
						->addAttributeToSelect('*')
						->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))
						->setPageSize($pageSize)
						->setCurPage($pageNo)
						->getData();
						
		return $cust_data;
	}

	private function getCustomers(){
		$content = '';
		$custCol = $this->getCustomerCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
		foreach($custCol as $cust) {
			$content .= $this->getCustomerInfo($cust['entity_id'],$cust['updated_at']);
		}
		return $content;
	}

	private function getCustomerInfo($id,$LUD){
		$cust = $this->getCustomerModel()->load($id);
		if(!$salutation = $cust->getPrefix()){$salutation = '';}
		if(!$taxvat = $cust->getTaxvat()){$taxvat = '';}
		$addressManager = \Magento\Framework\App\ObjectManager::getInstance();
		$custBillingAdd = $addressManager->create('Magento\Customer\Model\Address')->load($cust->getDefaultBilling());
		$custShippingAdd = $addressManager->create('Magento\Customer\Model\Address')->load($cust->getDefaultShipping());
		$streetBill = $custBillingAdd->getStreet();
		$streetShip = $custShippingAdd->getStreet();
		$cust_info = array(
							'Salutation' => htmlspecialchars($salutation),
							'FirstName' => htmlspecialchars($cust->getFirstname()),
							'LastName' => htmlspecialchars($cust->getLastname()),
							'WorkPhone'=>htmlspecialchars($custBillingAdd->getTelephone()),
							'Email'=>htmlspecialchars($cust->getEmail()),
							'OrganizationName'=>htmlspecialchars($custBillingAdd->getCompany()),
							'OrganizationBusinessNumber'=>htmlspecialchars($taxvat),
							'Addresses'=>array(
												'Address type="Billing"'=>array(
																'Line1'=>htmlspecialchars(isset($streetBill[0])?$streetBill[0]:""),
																'Line2'=>htmlspecialchars(isset($streetBill[1])?$streetBill[1]:""),
																'City'=>htmlspecialchars($custBillingAdd->getCity()),
																'PostCode'=>htmlspecialchars($custBillingAdd->getPostcode()),
																'State'=>htmlspecialchars($custBillingAdd->getRegion()),
																'CountryCode'=>htmlspecialchars($custBillingAdd->getCountryId())
																),
												'Address type="Shipping"'=>array(
																'Line1'=>htmlspecialchars(isset($streetShip[0])?$streetShip[0]:""),
																'Line2'=>htmlspecialchars(isset($streetShip[1])?$streetShip[1]:""),
																'City'=>htmlspecialchars($custShippingAdd->getCity()),
																'PostCode'=>htmlspecialchars($custShippingAdd->getPostcode()),
																'State'=>htmlspecialchars($custShippingAdd->getRegion()),
																'CountryCode'=>htmlspecialchars($custShippingAdd->getCountryId())
																)
											)	
						);
		return $this->getInfoXml('Contact',$id,$LUD,$cust_info);
	}

	/*** Customer API Functions End ***/	

	/*** Order API Functions Start ***/

	public function ordersAction(){
		if(!$this->getPageNoIsValid('Order')) {
			$this->xml .= "";
		} else {
			$this->xml .= $this->getOrders();
		}
	}

	private function getOrderModel(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		return $objectManager->create('Magento\Sales\Model\Order');
	}

	private function getOrderCollection($pageSize,$pageNo,$lstUpdTm){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$orderCollection = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
		$ord_data = $orderCollection->create()
				->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))
				->setPageSize($pageSize)
				->setCurPage($pageNo)
				->getData();		

		return $ord_data;
	}

	private function getOrders(){
		$content = '';
		$ordCol = $this->getOrderCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
		
		foreach($ordCol as $ord){
			
			// If it is an order that has been updated after submission, the order is cancelled and a new one is created

			$original_id = $ord['entity_id'];
			$content .= $this->getOrderInfo($ord['entity_id'],$original_id, $ord['updated_at']);
		}
		return $content;
	}
	
	// Try to match the tax rate ($rate) with the taxes applied to the order ($fullTaxInfo) to retrieve the name of the tax
	private function getTaxNameForItem($fullTaxInfo, $rate) {
		if (!isset($fullTaxInfo) || !is_array($fullTaxInfo) || count($fullTaxInfo)==0 || !is_numeric($rate)) {
			return "";
		}
		foreach($fullTaxInfo as $taxInfo) {
			if (is_array($taxInfo) && isset($taxInfo['rates']) && is_array($taxInfo['rates']) && count($taxInfo['rates']>0)) {
				foreach($taxInfo['rates'] as $taxRate) {
					if (is_array($taxRate) && isset($taxRate['code']) && isset($taxRate['percent']) && is_numeric($taxRate['percent']) && (abs((0.0 + $taxRate['percent']) - (0.0 + $rate)) < 0.01)) {
						return $taxRate['code'];
					}
				}
			}
		}
		return "";
	}
	
	public function getOrderItemInfo($product_id,$product_code,$item,$order){
		$item_arr_inner = array('ProductId'=>$product_id,
							'ProductCode'=>htmlspecialchars($product_code),
							'ProductName'=>htmlspecialchars($item->getName()),
							'Quantity'=>htmlspecialchars($item->getQtyOrdered()),
							'Price'=>htmlspecialchars($item->getBasePriceInclTax()),
							'UnitPriceExTax'=>htmlspecialchars($item->getBasePrice()));
		if ($item->getBaseDiscountAmount()>0) {$item_arr_inner['Discount'] = htmlspecialchars($item->getBaseDiscountAmount()/$item->getQtyOrdered());}
		if ($item->getBaseTaxAmount()>0) {$item_arr_inner['Taxes'] = array(
									'Tax'=>array(
									'TaxName'=>$this->getTaxNameForItem($order->getFullTaxInfo(), $item->getTaxPercent()),
									'TaxRate'=>htmlspecialchars((0.0 + $item->getTaxPercent())/100),
									'TaxAmount'=>htmlspecialchars($item->getBaseTaxAmount())));}

		$item_arr_inner['LineTotalIncTax']=$item->getBaseRowTotalInclTax();
		return $item_arr_inner;			
	}	
	
	private function getOrderInfo($id,$original_id, $LUD){
 		$order = $this->getOrderModel()->load($id);
 		$items = $order->getAllVisibleItems();
 		$items_arr = array();
		foreach($items as $index => $item){
			$product_id = $item->getProductId();
			$order_item = $this->getProductModel()->load($product_id);
			$type = $order_item->getTypeId();
			if($type != 'configurable' or $type != 'bundle')
 			{
				$product_id = $item->getProductId();
 				$product_code = $item->getSku();
				$item_arr_inner = $this->getOrderItemInfo($product_id,$product_code,$item,$order);
				$tax_rate = $item->getTaxPercent()/100;
				$items_arr['Item_' . $index]=$item_arr_inner;			
 			}				
			if($type == 'configurable')
 			{
 				$product_code = $item->getSku();
 				$product_id = $this->getProductModel()->getIdBySku($product_code);
				$item_arr_inner = $this->getOrderItemInfo($product_id,$product_code,$item,$order);
				$tax_rate = $item->getTaxPercent()/100;
				$items_arr['Item_' . $index]=$item_arr_inner;			
 			}			
			if($type == 'bundle')
			{
				$sku_type = $order_item->getSkuType();
				$price_type = $order_item->getPriceType();				
				if ($sku_type == 0) // If SKU is dynamically generated
				{	
					//No support for dinamically generated bundle SKU's
				}
				if ($sku_type == 1 || $sku_type == NULL) // If SKU is not dynamically generated
				{
							$product_code = $item->getData('sku');
							$product_id = $item->getData('product_id');
							$item_arr_inner = $this->getOrderItemInfo($product_id,$product_code,$item,$order);
							$items_arr['Item_' . $index]=$item_arr_inner;
				}		
			}
		}

		$payments = $order->getAllPayments();
		$payments_array = array();
		foreach($payments as $key=>$a_payment) {
			try {
				$paymentMethodName = $a_payment->getMethodInstance()->getCode();
			} catch (Exception $e) {
				// Do nothing for now...
			}
			$payments_array['PaymentMethod'] = array(
				'MethodName' => htmlspecialchars($paymentMethodName),
				'Amount'=>htmlspecialchars($a_payment->getBaseAmountPaid())
				);
		}

		$shippings_array = array();
		$carrier_code = '';
		$shipping_carrier = $order->getShippingCarrier();
		if (!is_null($shipping_carrier) && is_object($shipping_carrier)) {
			$carrier_code = $shipping_carrier->getCarrierCode();
		}
		
		$shippings_array = array(
			'ShippingMethod'=>htmlspecialchars($order->getShippingDescription()),
			//'ShippingMethod'=>htmlspecialchars($carrier_code),
			'Amount'=>htmlspecialchars($order->getBaseShippingAmount()+$order->getData('base_shipping_tax_amount')));
			if($order->getData('base_shipping_tax_amount') > 0)
			{
			$shipping_tax_rate = $order->getData('base_shipping_tax_amount')/$order->getBaseShippingAmount();
			$epsilon = 0.0005;			
				if(abs($shipping_tax_rate - $tax_rate) < $epsilon)
				{
					$shippings_array['Taxes']= array(
					'Tax'=>array(
					'TaxName'=>"",
					'TaxRate'=>htmlspecialchars($tax_rate),
					'TaxAmount'=>htmlspecialchars($order->getData('base_shipping_tax_amount'))));				
				}
				else
				{
					//Use the Shipping Country Tax. This when items are WO tax, we don't have a tax rate to compare. 	
				}
			}
			if($order->getData('base_shipping_discount_amount') > 0)
			{
				$shippings_array['Discount'] = htmlspecialchars($order->getBaseShippingDiscountAmount());
			}			
			
		$credits = $order->getCreditmemosCollection();
		$credits_array = array();

		foreach($credits as $key => $a_credit) {
			$comments_array = array();
			foreach ( $a_credit->getCommentsCollection() as $j => $a_comment) {
				$comments_array['Comment_' . $j . ' LastUpdated="' . htmlspecialchars($a_comment->getCreatedAt()) . '"'] = htmlspecialchars($a_comment->getComment());
			}
			$credits_array['Credit LastUpdated="' . htmlspecialchars($a_credit->getUpdatedAt()) . '"'] = array('Date'=>htmlspecialchars($a_credit->getCreatedAt()), 'Amount'=>htmlspecialchars($a_credit->getBaseGrandTotal()), 'Comments'=> $comments_array);
		}	
		
		// Contact Info - whether it is a registered user or a guest
		$custBillingAdd = $order->getBillingAddress();
		$custShippingAdd = $order->getShippingAddress();
		if (!$custShippingAdd && $custBillingAdd) {
			$custShippingAdd = $custBillingAdd;
		}
		if ($custShippingAdd && !$custBillingAdd) {
			$custBillingAdd = $custShippingAdd;
		}
		$contact_info = '';
		
		if ($custShippingAdd && $custBillingAdd) {
			$streetBill = $custBillingAdd->getStreet();
			$streetShip = $custShippingAdd->getStreet();
			if(!$salutation = $order->getData('customer_prefix')){$salutation = '';}
			if(!$taxvat = $order->getData('customer_taxvat')){$taxvat = '';}
			$customerFirstname = ($order->getCustomerFirstname() == '')?(($custBillingAdd->getFirstname()=='')?$custShippingAdd->getFirstname():$custBillingAdd->getFirstname()):$order->getCustomerFirstname();
			$customerLastname = ($order->getCustomerLastname() == '')?(($custBillingAdd->getLastname()=='')?$custShippingAdd->getLastname():$custBillingAdd->getLastname()):$order->getCustomerLastname();

			// Billing & Shipping Optional Addresses
			$addresses = array(
							'Address type="Billing"'=>array(
											'Salutation'=>htmlspecialchars(($salutation = $custBillingAdd->getData('prefix'))?$salutation:''),
											'FirstName'=>htmlspecialchars($custBillingAdd->getFirstname()),
											'LastName'=>htmlspecialchars($custBillingAdd->getLastname()),
											'OrganizationName'=>htmlspecialchars(($company = $custBillingAdd->getData('company'))?$company:''),
											'Line1'=>htmlspecialchars(isset($streetBill[0])?$streetBill[0]:""),
											'Line2'=>htmlspecialchars(isset($streetBill[1])?$streetBill[1]:""),
											'City'=>htmlspecialchars($custBillingAdd->getCity()),
											'PostCode'=>htmlspecialchars($custBillingAdd->getPostcode()),
											'State'=>htmlspecialchars($custBillingAdd->getRegion()),
											'CountryCode'=>htmlspecialchars($custBillingAdd->getCountryId())
											),
							'Address type="Shipping"'=>array(
											'Salutation'=>htmlspecialchars(($salutation = $custShippingAdd->getData('prefix'))?$salutation:''),
											'FirstName'=>htmlspecialchars($custShippingAdd->getFirstname()),
											'LastName'=>htmlspecialchars($custShippingAdd->getLastname()),
											'OrganizationName'=>htmlspecialchars(($company = $custShippingAdd->getData('company'))?$company:''),
											'Line1'=>htmlspecialchars(isset($streetShip[0])?$streetShip[0]:""),
											'Line2'=>htmlspecialchars(isset($streetShip[1])?$streetShip[1]:""),
											'City'=>htmlspecialchars($custShippingAdd->getCity()),
											'PostCode'=>htmlspecialchars($custShippingAdd->getPostcode()),
											'State'=>htmlspecialchars($custShippingAdd->getRegion()),
											'CountryCode'=>htmlspecialchars($custShippingAdd->getCountryId())
											)
						);
						
			$contact_info = array(
							'Salutation' => htmlspecialchars($salutation),
							'FirstName' => htmlspecialchars($customerFirstname),
							'LastName' => htmlspecialchars($customerLastname),
							'BillingFirstName' => htmlspecialchars($custBillingAdd->getFirstname()),
							'BillingLastName' => htmlspecialchars($custBillingAdd->getLastname()),
							'ShippingFirstName' => htmlspecialchars($custShippingAdd->getFirstname()),
							'ShippingLastName' => htmlspecialchars($custShippingAdd->getLastname()),
							'WorkPhone'=>htmlspecialchars($custBillingAdd->getTelephone()),
							'Email'=>htmlspecialchars($order->getData('customer_email')),
							'OrganizationName'=>htmlspecialchars($custBillingAdd->getCompany()),
							'OrganizationBusinessNumber'=>htmlspecialchars($taxvat),
							'Addresses'=>$addresses
						);
		} else {
			// If no contact info, then exclude this order and return
			return "";
		}

		// Contact tag
		if (is_null($order->getCustomerId())) {
			$contact_tag = 'Contact';
		} else {
			$contact_tag = 'Contact id="'. $order->getCustomerId() .'"';
		}

		$order_number = $order->getIncrementId();
		
		$currencyCode   = '';
		$currency       = $order->getBaseCurrency(); //$order object
		if (is_object($currency)) {
			$currencyCode = $currency->getCurrencyCode();
		}
		
		// OS-3835 & OS-3558 & OS-5925
		$customFields_array = array(
			'CustomField Name="CreditPoints"'=>$order->getData('creditpoint_amount'),
			'CustomField Name="OrderProcessingFeeExTax"'=>$order->getData('base_et_payment_extra_charge_excluding_tax'),
			'CustomField Name="OrderProcessingFee"'=>$order->getData('base_et_payment_extra_charge'),
			'CustomField Name="LastTransactionId"'=>$order->getPayment()->getLastTransId()
		);
		
		
		if($order->hasInvoices()){
			foreach($order->getInvoiceCollection() as $inv){
				$customFields_array['CustomField Name="Inv-' . $inv->getIncrementId() . '"'] = $inv->getCreatedAt();
			}
		}
		
		$replaces_order_number_key = 'ReplaceOrderNumber';
		$replaces_order_number = '';
		if (strpos($order_number,'-')) {
		 	$replaces_order_number = $order->getData('relation_parent_real_id');
			$replaced_order_id = $order->getData('relation_parent_id');
			$replaces_order_number_key = 'ReplaceOrderNumber Id="' . $replaced_order_id . '"';
		}

		$ord_info = array(
							'OrderNumber' => htmlspecialchars($order_number),
							$replaces_order_number_key => htmlspecialchars($replaces_order_number),
							'Date' => htmlspecialchars($order->getCreatedAt()),
							'Type'=>"Order",
							'Status'=>htmlspecialchars($order->getStatus()),
							'CurrencyCode'=>htmlspecialchars($currencyCode),
							'Notes'=>htmlspecialchars($order->getCustomerNote()),
							'Tags'=>'StoreName:' . htmlspecialchars($order->getStoreName()) .',',
							'Discounts'=>htmlspecialchars(0.0 + abs($order->getBaseDiscountAmount()) + abs($order->getBaseCreditDiscountAmount())),
							'Total'=>htmlspecialchars($order->getBaseGrandTotal()),
							$contact_tag => $contact_info,
							'Addresses' => $addresses,
							'Items'=>$items_arr,
							'Shipping'=>$shippings_array,
							'Payments'=>$payments_array,
							'Credits'=>$credits_array,
							'CustomFields'=>$customFields_array,
							//'Url'=>htmlspecialchars($url)
						);

						
		
		return $this->getXml('Order',array('id'=>$original_id,'LastUpdated'=>$LUD),$ord_info);
	}

	/*** Order API Functions End ***/

	/*** Configuration API Functions Start ***/

	public function configurationsAction(){
		$this->xml .= $this->getConfiguration();
	}

	private function getTaxRateModel(){		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();     
		return $objectManager->create('Magento\Tax\Model\Calculation\Rate');
	}
	
	private function getConfigCollection($pageSize,$pageNo,$lstUpdTm){
		
		//Active Payment methods
		$activeMethods = $this->_paymentModelConfig->getActiveMethods();
		$paymentMethods = array();
		foreach ($activeMethods as $paymentCode=>$paymentModel){
			$paymentTitle = $this->_appConfigScopeConfigInterface->getValue('payment/'.$paymentCode.'/title');
			$paymentMethods[]=array('Name'=>$paymentCode, 'Description'=>$paymentTitle);
		}
		
		// Existing Tax rates
		$trModel = $this->getTaxRateModel();
		$tr_data = $trModel->getCollection()->getData();
		$trs = array();
		foreach($tr_data as $tr){
			//$stateCode = Mage::getModel('directory/region')->load($tr['tax_region_id'])->getCode();
			$trs[]=array('Name'=>htmlspecialchars($tr['code']), 'Rate'=>(0.0 + $tr['rate'])/100, 'CountryCode'=>htmlspecialchars($tr['tax_country_id']), 'Zip'=>htmlspecialchars($tr['tax_postcode']));
		}
		
		// Active Shipping methods
		$carriers = $this->_shippingConfig->getActiveCarriers();
		$ShippingMethods=array();
        foreach ($carriers as $carrierCode => $carrierModel) {	
            $carrierTitle = $this->_appConfigScopeConfigInterface->getValue('carriers/' . $carrierCode . '/title',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$ShippingMethods[]=array('Name'=>htmlspecialchars($carrierCode), 'Description'=>htmlspecialchars(strip_tags($carrierTitle)));
        }
		
		//Order Statuses
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 	
		$orderstatuses = $objectManager->create('Magento\Sales\Model\Order\Status')->getResourceCollection()->getData();
		foreach ($orderstatuses as $orderstatus){	
			$OrderStatuses[]=array('Name'=>$orderstatus['status'], 'Description'=>htmlspecialchars($orderstatus['label']));
		}		
		
		/*	
		// TimeZones
		$times = '';
		$datetime = Zend_Date::now();
		$datetime->setLocale(Mage::getStoreConfig(
		             Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE));
		$localUTCtime = $datetime->get(Zend_Date::ISO_8601);
		$datetime->setLocale(Mage::getStoreConfig(
		             Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE))
		         ->setTimezone(Mage::getStoreConfig(
		             Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));		 
		$localtime = $datetime->get(Zend_Date::ISO_8601);
		$times = array('LocalTime'=>$localtime,'LocalUTCTime'=>$localUTCtime);
		*/
		
		//Plugin Features
		$features_array = array('BatchStockUpdates'=>'true');
		$price_setting = $this->_appConfigScopeConfigInterface->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($price_setting === '0')
			{
				$features['IsSellingPriceIncludingTax'] = 'false';
			}
		else
			{
				$features['IsSellingPriceIncludingTax'] = 'true';
			}		
		foreach($features_array as $feature => $status) {
			$features[] = array('Name'=>$feature,'Value'=>$status);
		}
		//$config=array('Times'=>$times, 'PaymentGateways'=>$paymentMethods,'TaxCodes'=>$trs,'ShippingMethods'=>$ShippingMethods, 'OrderStatuses'=>$OrderStatuses, 'Categories'=>$Categories, 'PluginFeatures'=>$features);
		$config=array('PaymentGateways'=>$paymentMethods,'TaxCodes'=>$trs,'ShippingMethods'=>$ShippingMethods, 'OrderStatuses'=>$OrderStatuses, 'PluginFeatures'=>$features);
		return $config;
	}

	private function getConfiguration(){
		$content = '';
		$confCol = $this->getConfigCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
		$count = 0;
		$found = 0;
		$pageNo = (($this->pageNo-1)*$this->pageSize)+1;
		$endpageNo = $this->pageSize*$this->pageNo;
		foreach($confCol as $key=>$conf){
			$count++;

			if(($pageNo<=$count) AND ($count<=$endpageNo)) {
				$content .= $this->getConfigInfo($key,$conf);
				$found = 1;
			}
		}
		if($found == 0) {
			$content .= '';
		}
		return $content;
	}

	private function getConfigInfo($key,$conf){

		return $this->getInfoXml($key,false,null,$conf);

	}

	/*** Configuration API Functions End ***/	

	/*** Order Shipping Tracking API Functions Start ***/

	public function shippingtrackingAction(){
		// Parse posted parameters ShippingTrackingId, OrderNumber, Date, TrackingCode, CarrierCode, CarrierName, Notes
		$xmlRequest = new \SimpleXmlElement(file_get_contents("php://input"));
		if ($xmlRequest->getName()==='OrderShippingTracking') {
			foreach ($xmlRequest->attributes() as $attr) {
				if ($attr->getName() === 'Id') {
					$OrderId = "" . $attr;
				}
			}
			foreach ($xmlRequest->children() as $child) {
				switch ($child->getName()) {
					case 'OrderNumber':
						$OrderIncrementId = $child;
						break;
					case 'Date':
						$Date = (string)$child;
						break;
					case 'TrackingCode':
						$TrackingCode = (string)$child;
						break;
					case 'CarrierCode':
						$CarrierCode = (string)$child;
						break;
					case 'CarrierName':
						$CarrierName = (string)$child;
						break;
					case 'Notes':
						$Notes = (string)$child;
						break;
					default:
						// Not interested
						break;
				}
			}
			if ( ($OrderId != '') ) {
				try {
					$order = $this->getOrderModel()->load($OrderId);
					if(!$order->hasShipments())
					{
						if($order->canShip()) {
							$ship = $this->_shipmentFactory->create($order)->save();
							$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
							$track = $objectManager->create('Magento\Sales\Model\Order\Shipment\Track')->setShipment($ship);
							$track->setCreatedAt($Date);
							$track->setOrderId($ship->getOrderId());
							$track->setTrackNumber((isset($TrackingCode)) ? $TrackingCode : '-' );
							$track->setTitle((isset($CarrierName)) ? $CarrierName : '-' );
							$track->setCarrierCode('custom');
							$track->setDescription((isset($Notes)) ? $Notes : '-' );
							$track->save();	
						}						
					}
					else
					{
						$shipment_collection = $order->getShipmentsCollection();	
										
						foreach($shipment_collection as $sc) {
							$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
							$shipment = $objectManager->create('Magento\Sales\Model\Order\Shipment');
							$shipment->load($sc->getId());
							if($shipment->getId() != '') {
								if(!$shipment->getTracks())
								{
									$track = $objectManager->create('Magento\Sales\Model\Order\Shipment\Track')->setShipment($shipment);
									$track->setCreatedAt($Date);
									$track->setOrderId($shipment->getOrderId());
									$track->setTrackNumber((isset($TrackingCode)) ? $TrackingCode : '-' );
									$track->setTitle((isset($CarrierName)) ? $CarrierName : '-' );
									$track->setCarrierCode('custom');
									$track->setDescription((isset($Notes)) ? $Notes : '-' );
									$track->save();									
								}
								else
								{
									//Ignore shipping tracking update if order already has tracking info assigned. 
									/*
									$track_collection = $shipment->getTracksCollection();
									foreach($track_collection as $tc)
									{
										$track = $objectManager->create('Magento\Sales\Model\Order\Shipment\Track');
										$track->load($tc->getId());
										$track->setCreatedAt($Date);
										$track->setOrderId($shipment->getOrderId());
										$track->setTrackNumber((isset($TrackingCode)) ? $TrackingCode : '-' );
										$track->setTitle((isset($CarrierName)) ? $CarrierName : '-' );
										$track->setCarrierCode('custom');
										$track->setDescription((isset($Notes)) ? $Notes : '-' );
										$track->save();										
									}*/	
								}	
							}
						}
						if($shipment){
							if(!$shipment->getEmailSent()){
							$shipment->setSendEmail(true);
							$shipment->setEmailSent(true);
							$shipment->save();
							}
						$order->setStatus('complete')->save();
						}						
					}						
					$this->xml .= '<Success>Operation Succeeded</Success>';
				} catch (Exception $ex) {
					$this->xml .= '<Error>Shipping tracking update failed: ' . $ex . '. OrderId=' . $OrderId . ' OrderIncrementalId=' . $OrderIncrementId . '</Error>';
				}
			} else {
				$this->xml .= '<Error>Wrong parameters: Order Id = ' . $OrderId . '</Error>';
			}
		} else {
			$this->xml .= '<Error>Wrong xml format</Error>';
		}
	}

	/*** Order Shipping Tracking API Functions End ***/

	/*** Update Stock API Functions Start ***/

	public function updateStockAction() {
		// Parse posted parameters StockUpdateId, ProductCode, StockAtHand, StockAllocated, StockAvailable
		$xmlRequest = new \SimpleXmlElement(file_get_contents("php://input"));
		$stockUpdateRequests = array();
		$batchMode='false';
		if ($xmlRequest->getName()==='ProductStockUpdate') {
			// Single product stock update
			$stockUpdateRequests[] = $this->parseSingleStockUpdateRequest($xmlRequest);
		} elseif ($xmlRequest->getName()==='ProductStockUpdates') {
			// Multiple product stock update
			$batchMode='true';
			foreach ($xmlRequest->children() as $aXmlRequest) {
				$stockUpdateRequests[] = $this->parseSingleStockUpdateRequest($aXmlRequest);
			}
		} else {
			// Wrong format
		}

	// Xml Response depends on batchMode
	//$this->xml .= ($batchMode=='true')?'<ProductStockUpdates>':'';
	foreach ($stockUpdateRequests as $stockUpdateRequest) {
		$this->xml .= ($batchMode=='true')?'<Response Id="' . $stockUpdateRequest['Id'] .'">':'';
			if ($stockUpdateRequest['Id']  != '') {
				$product = $this->getProductModel()->load($stockUpdateRequest['Id']);
				if (is_object($product)) {
				/*
				$stockItem = $this->_stockRegistry->getStockItem($product->getEntityId()); 
				$stockItem->setData('qty', $stockUpdateRequest['StockAvailable']);
				$stockItem->setData('is_in_stock', 1);*/
				$product->setStockData(['qty' => $stockUpdateRequest['StockAvailable'], 'is_in_stock' => $stockUpdateRequest['StockAvailable']]);
				$product->setQuantityAndStockStatus(['qty' => $stockUpdateRequest['StockAvailable'], 'is_in_stock' => $stockUpdateRequest['StockAvailable']]);
				try {
				//$stockItem->save();
				$product->save();
				$this->xml .= '<Success>Operation Succeeded. Batch mode=' . $batchMode . '</Success>';
				} catch (Exception $ex) 
				{
					$this->xml .= '<Error>Stock update failed: ' . htmlspecialchars($ex) . '</Error>';	
				}
				}
				else {	
					$this->xml .= '<Error>Wrong parameters: Product is not object. Id="' . $stockUpdateRequest['Id']  . '" StockAvailable=' . $stockUpdateRequest['StockAvailable'] . '</Error>';
				}
			} else {
				$this->xml .= '<Error>Wrong parameters: Id="' . $stockUpdateRequest['Id']  . '" StockAvailable=' . $stockUpdateRequest['StockAvailable'] . '</Error>';
			}
		$this->xml .= ($batchMode=='true')?'</Response>':'';
	}
	//$this->xml .= ($batchMode=='true')?'</ProductStockUpdates>':'';
	}
	
	/*** Update Stock API Functions End ***/	

	/*** General Functions Start ***/
	private function parseSingleStockUpdateRequest($aRequest) {
		$stockUpdateRequest = array();
		if (!is_null($aRequest) && $aRequest->getName()==='ProductStockUpdate') {
			foreach ($aRequest->attributes() as $attr) {
				if ($attr->getName() === 'Id') {
					$stockUpdateRequest['Id'] = $attr;
				}
			}		
			foreach ($aRequest->children() as $child) {
				switch ($child->getName()) {
					case 'StockAtHand':
						$stockUpdateRequest['StockAtHand'] = $child;
						break;
					case 'StockAllocated':
						$stockUpdateRequest['StockAllocated'] = $child;
						break;
					case 'StockAvailable':
						$stockUpdateRequest['StockAvailable'] = (float) $child;
						break;
					default:
						// Not interested
						break;
				}
			}
			$stockUpdateRequest;
		}
		return $stockUpdateRequest;
	}
	
	private function getInfoXml($entity,$id,$LUD,$info){
		if(!$id){
			$xmlStr = "<$entity>";
		}elseif($id and $LUD == null){
			$xmlStr = "<$entity id=\"$id\">";
		}else{
			$xmlStr = "<$entity id=\"$id\" LastUpdated=\"$LUD\">";
		}
		if(is_array($info)){
			foreach($info as $key=>$val){
				if(!(is_array($val))){
					$xmlStr .= "<$key>$val";
					$key_arr = explode(' ',$key);
					$xmlStr .= "</$key_arr[0]>";
				}else{
					if($entity == 'PaymentGateways'){
						$key = 'PaymentGateway';
					}elseif($entity == 'TaxCodes'){
						$key = 'TaxCode';
					}elseif($entity == 'ShippingMethods'){
						$key = 'ShippingMethod';
					}elseif($entity == 'Categories'){
						$key = 'Category';
					}elseif($entity == 'OrderStatuses'){
						$key = 'OrderStatus';
					}elseif($entity == 'PluginFeatures'){
						$key = 'PluginFeature';
					}					
					$xmlStr .= "<$key>";
					foreach($val as $k=>$v){
						if(!(is_array($v))){
							$xmlStr .= "<$k>$v</$k>";
						}else{
							if($entity == 'Order' and $key == 'Items' and is_int($k)){
								$k = 'Item';
							}elseif($entity == 'Product' and $key == 'ComboItems' and is_int($k)){
								$k = 'ComboItem';
							}elseif($entity == 'Product' and $key == 'Options' and is_int($k)){
								$k = 'Option';
							}
							$xmlStr .= "<$k>";
							foreach($v as $k2=>$v2){
								if($k2 != 'tagAttribs'){
									$xmlStr .= "<$k2>$v2</$k2>";
								}
							}
							$k_arr = explode(' ',$k);
							$xmlStr .= "</$k_arr[0]>";
						}
					}
					$key_arr = explode(' ',$key);
					$xmlStr .= "</$key_arr[0]>";
				}
			}
		}
		$xmlStr .= "</$entity>";
		return $xmlStr;
	}

	private function getXml($entity,$attributes=array(),$info) {
		$xmlStr = '';
		if (!is_null($attributes) && is_Array($attributes) && count($attributes)>0) {
			$xmlStr .= '<' . $entity;
			foreach ($attributes as $name => $value) {
				$xmlStr .= ' ' . $name . '="' . $value .'"';
			}
			$xmlStr .= '>';
		} else {
			$xmlStr .= "<$entity>";
		}
		if (is_array($info)) {
			foreach($info as $key=>$val) {
				if (strrpos($key,'_')) {
					$begin = substr($key,0,strrpos($key,'_'));
					$end = substr($key,strrpos($key,'_'));
					($first_sapce = strpos($end,' '))?$end = substr($end,$first_sapce):$end = '';
					$key = $begin . $end;
				}
				$xmlStr .= $this->getXml($key,null,$val);
			}
		} else {
			$xmlStr .= $info;
		}
		$closingTag = explode(' ', $entity);
		$xmlStr .= '</' . $closingTag[0] . '>';

		return $xmlStr;
	}
	
	private function getAccessKey(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$key = $objectManager->create('Onesaas\Connect\Model\Resource\Key\Collection');
		$data = $key->getData();
		return $data[0]['key'];
	}	
	
	private function verifyAccessKey($userKey){
		$key = $this->getAccessKey();
		if($userKey === $key){
			return true;
		}	
	}	
	
	private function getOneSaasConnectVersion(){
		return '2.2.0.6';
	}	
	
	private function getPageNoIsValid($entity){
		if($entity == 'Product') {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
			$productCollections = $productCollection->create()
						->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))
						->getData();
						
			$proCount = ceil(count($productCollections)/$this->pageSize);

			if($this->pageNo>$proCount) {
				return False;
			} else {
      			return True;
      		}
		}
		if($entity == 'Contact') {

			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$customerCollection = $objectManager->create('Magento\Customer\Model\ResourceModel\Customer\CollectionFactory');
			$customerCollections = $customerCollection->create()
								->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))
								->getData();
								
			$custCount = ceil(count($customerCollections)/$this->pageSize);

			if($this->pageNo>$custCount)
				return False;
			else

      			return True;

		}
		if($entity == 'Order') {
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$orderCollection = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
			$orderCollections = $orderCollection->create()
						->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))
						->getData();
						
			$orderCount = ceil(count($orderCollections)/$this->pageSize);

			if($this->pageNo>$orderCount)
				return False;
			else

      			return True;

		}		
	}	
}
