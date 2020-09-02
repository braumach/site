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

namespace Ced\Aramexshipping\Controller\Adminhtml\Shipment;
use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
class PrintAction extends \Magento\Backend\App\Action
{
    protected $_moduleReader;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registerInterface,
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registerInterface;
        $this->_moduleReader = $moduleReader;
        parent::__construct($context);
    }

    public function execute()
    {
        $shipment_id = $this->getRequest()->getParam('id');
        $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipment_id);
        foreach($shipment->getAllTracks() as $tracknum)
        {
            $awb = $tracknum->getNumber();
        }
        $account_number = $this->_scopeConfig->getValue('carriers/aramexshipping/account_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $account_country_code = $this->_scopeConfig->getValue('carriers/aramexshipping/account_country_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $account_entity = $this->_scopeConfig->getValue('carriers/aramexshipping/account_entity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $account_pin = $this->_scopeConfig->getValue('carriers/aramexshipping/account_pin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $account_username = $this->_scopeConfig->getValue('carriers/aramexshipping/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $account_password = $this->_scopeConfig->getValue('carriers/aramexshipping/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $params = [
                    'ClientInfo'            => [
                            'AccountCountryCode' => $account_country_code,
                            'AccountEntity' => $account_entity ,
                            'AccountNumber' => $account_number ,
                            'AccountPin' => $account_pin ,
                            'UserName' => $account_username ,
                            'Password' => $account_password ,
                            'Version' => 'v1.0' 
                    ],
                    'Transaction'           => [
                        'Reference1'            => '001',
                        'Reference2'            => '',
                        'Reference3'            => '',
                        'Reference4'            => '',
                        'Reference5'            => '',
                    ],
                    'LabelInfo'             => [
                        'ReportID'              => '9201',
                        'ReportType'            => 'URL',
                    ],
                ];
        $params['ShipmentNumber'] = $awb;
        $wsdlPath = $this->_moduleReader->getModuleDir('etc', 'Ced_Aramexshipping') . '/'. 'wsdl';
        $wsdl = $wsdlPath . '/' . 'shipping-services-api-wsdl.wsdl';
        $soapClient = new \SoapClient($wsdl);
        try {
            $auth_call = $soapClient->PrintLabel($params);
            if($auth_call->HasErrors){

                die('aaa');
            }
            $shipment_id = $this->getRequest()->getParam('id');
            $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipment_id);
            $_order = $shipment->getOrder();
            $filepath=$auth_call->ShipmentLabel->LabelURL;
            $name="{$_order->getIncrementId()}-shipment-label.pdf";
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="'.$name.'"');
            header( "HTTP/1.1 301 Moved Permanently" ); 
            header('Location: ' . $filepath);
            exit();
        }catch(\Exception $e){
            die($e);
        }
    }
}
