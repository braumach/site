<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Ced
 * @package     Ced_Aramexshipping
 * @author   CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Ced\Aramexshipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Module\Dir;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Ups\Helper\Config;
use Magento\Framework\Xml\Security;

class Aramex extends \Magento\Shipping\Model\Carrier\AbstractCarrierOnline implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
  const EXPRESS                   = 'EXP';
  const DOMESTIC                  = 'DOM';
  const PAYMENT_TYPE_PREPAID      = 'P';
  const PRODT_ON_DELIVERY         = 'OND';
  const CODE = 'aramexshipping';
    
    protected $_code = self::CODE;
    protected $_request;
    protected $_result;
    protected $_baseCurrencyRate;
    protected $_xmlAccessRequest;
    protected $_localeFormat;
    protected $_logger;
    protected $configHelper;
    protected $_rateMethodFactory;
    protected $_rateResultFactory;
    protected $_objectManager;
    protected $scopeConfig;
    protected $_isFixed = true;

    /**
     * Path to wsdl file of rate service
     *
     * @var string
     */
    protected $_rateServiceWsdl;
    
    public function __construct(
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
      \Psr\Log\LoggerInterface $logger,
      Security $xmlSecurity,
      \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
      \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
      \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
      \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
      \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
      \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
      \Magento\Directory\Model\RegionFactory $regionFactory,
      \Magento\Directory\Model\CountryFactory $countryFactory,
      \Magento\Directory\Model\CurrencyFactory $currencyFactory,
      \Magento\Directory\Helper\Data $directoryData,
      \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
      \Magento\Framework\Locale\FormatInterface $localeFormat,
      Config $configHelper,
      \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
      \Magento\Framework\ObjectManagerInterface $objectManager,
      \Magento\Framework\Module\Dir\Reader $configReader,
      array $data = []
    ){
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_logger = $logger;
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
        $wsdlBasePath = $configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Ced_Aramexshipping') . '/wsdl/';
        $this->_rateServiceWsdl = $wsdlBasePath . 'aramex_rates_calculator_service.wsdl';
    }
    
    /**
     * Create soap client with selected wsdl
     *
     * @param string $wsdl
     * @param bool|int $trace
     * @return \SoapClient
     */
    protected function _createSoapClient($wsdl, $trace = false)
    {
      $client = new \SoapClient($wsdl, ['trace' => $trace]);
      return $client;
    }
    
    
    /**
     * Create rate soap client
     *
     * @return \SoapClient
     */
    protected function _createRateSoapClient()
    {
      return $this->_createSoapClient($this->_rateServiceWsdl);
    }

    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
      
    }
    
    
    public function collectRates(RateRequest $request)
    {
      if (!$this->getConfigData('active')) {
            return false;
        }
        
       $allowed_methods = $this->_objectManager->create('Ced\Aramexshipping\Model\System\Config\Source\Producttypes')->toKeyArray();
       $admin_allowed_methods = explode(',',$this->getConfigData('product_type'));
       $admin_allowed_methods = array_flip($admin_allowed_methods);
       $allowed_methods = array_intersect_key($allowed_methods,$admin_allowed_methods);
       $destinationData = array(
          'StateOrProvinceCode' => $request->getDestRegion(),
          'City'          => $request->getDestCity(),
          'PostCode'        => $request->getDestPostcode() ,
          'CountryCode'     => $request->getDestCountryId()
       );
       //print_r($destinationData);die;
       
       $orgincity = $this->scopeConfig->getValue('shipping/origin/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
       $oricountrycode = $this->scopeConfig->getValue('shipping/origin/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
       $oripostcode = $this->scopeConfig->getValue('shipping/origin/postcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
     $oriregionid = $this->scopeConfig->getValue('shipping/origin/region_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
       $quoteId = $this->_objectManager->create('Magento\Checkout\Model\Session')->getQuoteId();
     //  $quote = $this->_objectManager->create("Magento\Sales\Model\Quote")->load($quoteId);
       $items = $request->getAllItems();
       $weight = 0;
       $quantity = 0;
       foreach($items as $item){
        $quantity+= $item->getQty();
        $weight+= $item->getWeight()*$item->getQty();
       }
       $requestData = array(
          'packageWeight' => $weight,
          'packageQty'    => $quantity
       );
       $productGroup = $request->getDestCountryId() == $oricountrycode ?
       self::DOMESTIC : self::EXPRESS;
       $productType = $productGroup == self::DOMESTIC ?
       self::PRODT_ON_DELIVERY : $this->getConfigData('product_type');
       $params = array(
          'ClientInfo'        => array(
              'AccountCountryCode'  => $this->getConfigData('account_country_code'),
              'AccountEntity'     => $this->getConfigData('account_entity'),
              'AccountNumber'     => $this->getConfigData('account_number'),
              'AccountPin'      => $this->getConfigData('account_pin'),
              'UserName'        => $this->getConfigData('username'),
              'Password'        => $this->getConfigData('password'),
              'Version'       => 'v1.0'
          ),
       
          'Transaction'       => array(
              'Reference1' => '001'
          ),
       
          'OriginAddress'     => array(
              'StateOrProvinceCode' =>$oriregionid,
              'City'          =>$orgincity,
              'PostCode'        =>$oripostcode,
              'CountryCode'     =>$oricountrycode,
          ),
       
          'DestinationAddress'  => $destinationData,
          'ShipmentDetails'   => array(
              'PaymentType'      => self::PAYMENT_TYPE_PREPAID,
              'ProductGroup'       => $productGroup,
              'ProductType'      => '',
              'ActualWeight'       => array('Value' => $requestData['packageWeight'], 'Unit' => $this->getConfigData('unit_of_measure')),
              'ChargeableWeight'       => array('Value' => $requestData['packageWeight'], 'Unit' => $this->getConfigData('unit_of_measure')),
              'NumberOfPieces'     => $requestData['packageQty']
          )
       );
       //print_r($params);die;
       $soapClient = $this->_createRateSoapClient();
       foreach($allowed_methods as $m_value =>$m_title)        
       {
        $params['ShipmentDetails']['ProductType'] = $m_value;
        try{
          $results = $soapClient->CalculateRate($params);
          //var_dump($results);die;
          $currentcurrency = $results->TotalAmount->CurrencyCode;
          $price = $results->TotalAmount->Value;
          $getprice = $this->_objectManager->get('Ced\Aramexshipping\Helper\Data')->convertRateCurrency($price, $currentcurrency);
          if($results->HasErrors){
            $response['type']='error';
          }else{
            $response['type']='success';
            $priceArr[$m_value] = array('label' => $m_title, 'amount'=> $getprice['price']);
        
          }
        } catch (\Exception $e) {
          $response['type']='error';
          $response['error']=$e->getMessage();
        }
       }
       $result = $this->_rateResultFactory->create();
       $defaults = $this->getDefaults();
       if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$values) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier($this->_code);
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);               
                $rate->setMethodTitle('Aramex - '.$values['label']);
                $rate->setCost($values['amount']);
                $rate->setPrice($values['amount']);
                $result->append($rate);
            }
        }
        return $result; 
    }
    
    public function getAllowedMethods()
    {
      return [$this->_code=> $this->getConfigData('name')];
    }

    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request){
      return $this;
    }
    
}