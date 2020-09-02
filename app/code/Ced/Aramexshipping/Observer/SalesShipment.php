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
 * @package     Ced_Aramexshipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Aramexshipping\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesShipment implements ObserverInterface
{
     protected $_registry = null;
     protected $_storeManager;
     protected $_moduleReader;

   public function __construct (        
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        $this->_objectManager=$objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_moduleReader = $moduleReader;
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$shipment = $observer->getEvent()->getShipment ();
		$order = $shipment->getOrder ();
		$shippingMethod = $order->getShippingMethod ();
		$s = explode ( "~", $shippingMethod );
		$shippingMethod = $s [0];
		if (strpos($shippingMethod, 'aramexshipping') !== false) {
		    $wsdlPath = $this->_moduleReader->getModuleDir('etc', 'Ced_Aramexshipping') . '/'. 'wsdl';
            $wsdl = $wsdlPath . '/' . 'shipping-services-api-wsdl.wsdl';

            $account_number = $this->_scopeConfig->getValue('carriers/aramexshipping/account_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$account_country_code = $this->_scopeConfig->getValue('carriers/aramexshipping/account_country_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$account_entity = $this->_scopeConfig->getValue('carriers/aramexshipping/account_entity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$account_pin = $this->_scopeConfig->getValue('carriers/aramexshipping/account_pin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$account_username = $this->_scopeConfig->getValue('carriers/aramexshipping/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$account_password = $this->_scopeConfig->getValue('carriers/aramexshipping/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$weight_unit = $this->_scopeConfig->getValue('carriers/aramexshipping/unit_of_measure', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$store_country  = $this->_scopeConfig->getValue('shipping/origin/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$store_city  = $this->_scopeConfig->getValue('shipping/origin/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$store_zip  = $this->_scopeConfig->getValue('shipping/origin/postcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$store_street1 = $this->_scopeConfig->getValue('shipping/origin/street_line1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$store_street2 = $this->_scopeConfig->getValue('shipping/origin/street_line2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			// customer details
			$shippingaddress = $order->getShippingAddress ();
			$customer_country = $shippingaddress->getCountryId ();
			$customer_postcode = $shippingaddress->getPostcode ();
			$customer_city = $shippingaddress->getCity ();
			$items = $order->getAllItems();
			$totalWeight = 0;
			$totalItems = 0;
			$totalPrice = 0;
			$description = '';
			foreach($items as $item){
				$qty = $item->getQtyOrdered();
				if($item->getWeight() != 0){
					$weight =  $item->getWeight()* $qty;
				} else {
					$weight =  0.5*$qty;
				}
				 
				$totalWeight 	+= $weight;
				$totalItems 	+= $qty;
				$totalPrice  += $item->getBaseRowTotal();
				$description .= $item->getProduct()->getName()."||";
				 
			}


			$params = ['Shipments' => [
							'Shipment' => [
									'Shipper' => [
											'Reference1' => 'Ref 111111',
											'Reference2' => 'Ref 222222',
											'AccountNumber' => $account_number,
											'PartyAddress' => [
													'Line1' => $store_street1,
													'Line2' => $store_street2,
													'Line3' => '',
													'City' => $store_city,
													'StateOrProvinceCode' => '',
													'PostCode' => $store_zip,
													'CountryCode' => $store_country 
											],
											'Contact' => [
													'Department' => '',
													'PersonName' => 'Michael',
													'Title' => '',
													'CompanyName' => 'Aramex',
													'PhoneNumber1' => '5555555',
													'PhoneNumber1Ext' => '125',
													'PhoneNumber2' => '',
													'PhoneNumber2Ext' => '',
													'FaxNumber' => '',
													'CellPhone' => '07777777',
													'EmailAddress' => 'michael@aramex.com',
													'Type' => '' 
											] 
									],
									
									'Consignee' => [
											'Reference1' => 'Ref 333333',
											'Reference2' => 'Ref 444444',
											'AccountNumber' => '',
											'PartyAddress' => [
													'Line1' => '15 ABC St',
													'Line2' => '',
													'Line3' => '',
													'City' => $customer_city,
													'StateOrProvinceCode' => '',
													'PostCode' => $customer_postcode,
													'CountryCode' => $customer_country 
											],
											
											'Contact' => [
													'Department' => '',
													'PersonName' => 'Mazen',
													'Title' => '',
													'CompanyName' => 'Aramex',
													'PhoneNumber1' => '6666666',
													'PhoneNumber1Ext' => '155',
													'PhoneNumber2' => '',
													'PhoneNumber2Ext' => '',
													'FaxNumber' => '',
													'CellPhone' => '2365987',
													'EmailAddress' => 'mazen@aramex.com',
													'Type' => '' 
											] 
									],
									
									'ThirdParty' => [
											'Reference1' => '',
											'Reference2' => '',
											'AccountNumber' => '',
											'PartyAddress' => [
													'Line1' => '',
													'Line2' => '',
													'Line3' => '',
													'City' => '',
													'StateOrProvinceCode' => '',
													'PostCode' => '',
													'CountryCode' => '' 
											],
											'Contact' => [
													'Department' => '',
													'PersonName' => '',
													'Title' => '',
													'CompanyName' => '',
													'PhoneNumber1' => '',
													'PhoneNumber1Ext' => '',
													'PhoneNumber2' => '',
													'PhoneNumber2Ext' => '',
													'FaxNumber' => '',
													'CellPhone' => '',
													'EmailAddress' => '',
													'Type' => '' 
											] 
									],
									
									'Reference1' => 'Shpt 0001',
									'Reference2' => '',
									'Reference3' => '',
									'ForeignHAWB' => $order->getIncrementId () . rand ( 10, 100 ),
									'TransportType' => 0,
									'ShippingDateTime' => time (),
									'DueDate' => time (),
									'PickupLocation' => 'Reception',
									'PickupGUID' => '',
									'Comments' => 'Shpt 0001',
									'AccountingInstrcutions' => '',
									'OperationsInstructions' => '',
									
									'Details' => [
											'Dimensions' => [
													'Length' => 10,
													'Width' => 10,
													'Height' => 10,
													'Unit' => 'cm' 
											]
											,
											
											'ActualWeight' => [
													'Value' => $totalWeight,
													'Unit' => $weight_unit 
											],
											
											'ProductGroup' => 'EXP',
											'ProductType' => 'PDX',
											'PaymentType' => 'P',
											'PaymentOptions' => '',
											'Services' => '',
											'NumberOfPieces' => $totalItems,
											'DescriptionOfGoods' => 'Docs',
											'GoodsOriginCountry' => 'Jo',
											
											'CashOnDeliveryAmount' => [
													'Value' => 0,
													'CurrencyCode' => '' 
											],
											
											'InsuranceAmount' => [
													'Value' => 0,
													'CurrencyCode' => '' 
											],
											
											'CollectAmount' => [
													'Value' => 0,
													'CurrencyCode' => '' 
											],
											
											'CashAdditionalAmount' => [
													'Value' => 0,
													'CurrencyCode' => '' 
											],
											
											'CashAdditionalAmountDescription' => '',
											
											'CustomsValueAmount' => [
													'Value' => 0,
													'CurrencyCode' => '' 
											],
											
											'Items' => []

											 
									] 
							] 
					],
			

					'ClientInfo' => [
							'AccountCountryCode' => $account_country_code,
							'AccountEntity' => $account_entity ,
							'AccountNumber' => $account_number ,
							'AccountPin' => $account_pin ,
							'UserName' => $account_username ,
							'Password' => $account_password ,
							'Version' => 'v1.0' 
					],
					
					'Transaction' => [
							'Reference1' => '001',
							'Reference2' => '',
							'Reference3' => '',
							'Reference4' => '',
							'Reference5' => '' 
					],
					'LabelInfo' => [
							'ReportID' => 9201,
							'ReportType' => 'URL' 
					] 
			];
			
			$params ['Shipments'] ['Shipment'] ['Details'] ['Items'] [] = [
					'PackageType' => 'Box',
					'Quantity' => $totalItems,
					'Weight' => [
							'Value' => $totalWeight,
							'Unit' => $weight_unit 
					],
					'Comments' => 'Docs',
					'Reference' => '' 
			];

			$soapClient = new \SoapClient($wsdl);
			try{
				$auth_call = $soapClient->CreateShipments ( $params );
				if ($auth_call->HasErrors) {
					print_r($auth_call);die;
				}else{
					$awbno = $auth_call->Shipments->ProcessedShipment->ID;
					$shipment = $observer->getEvent()->getShipment();
	                $track = $this->_objectManager->create(
	                'Magento\Sales\Model\Order\Shipment\Track'
	                                            )->setNumber(
	                                                $awbno
	                                            )->setCarrierCode(
	                                                'aramexshipping'
	                                            )->setTitle(
	                                                'Aramex Shipping'
	                                            );
	                $shipment->addTrack($track);

				}
				
			}catch(\Exception $e){
				print_r($e->getMessage());die;
			}
		}       
    }
}
