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

namespace Ced\Aramexshipping\Helper;

/**
 * Configuration data of carrier
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_objectManager;
	
	protected $_storeManager;
	
	public function __construct(\Magento\Framework\App\Helper\Context $context,
			\Magento\Framework\ObjectManagerInterface $objectManager,
			\Magento\Store\Model\StoreManagerInterface $storeManager
			)
	{
		$this->_objectManager = $objectManager;
		$this->_storeManager = $storeManager;
		parent::__construct($context);		
	}
	
	/**
	 * Convert currency
	 *
	 */
	public function currencyConvert($price, $from, $to, $output = '', $round = null)
	{
		$from = strtoupper($from);
		$to = strtoupper($to);
	
		$baseCurrencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
	
		if ('_BASE_' == $from) {
			$from = $baseCurrencyCode;
		} elseif ('_CURRENT_' == $from) {
			$from = $currentCurrencyCode;
		}
	
		if ('_BASE_' == $to) {
			$to = $baseCurrencyCode;
		} elseif ('_CURRENT_' == $to) {
			$to = $currentCurrencyCode;
		}
	
		$output = strtolower($output);
	
		$error  = false;
		$result = array('price' => $price, 'currency' => $from);
	
		if ($from != $to) {
			$allowedCurrencies = $this->_objectManager->create('Magento\Directory\Model\Currency')->getConfigAllowCurrencies();
			$rates = $this->_objectManager->create('Magento\Directory\Model\Currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
	
			if (empty($rates) || !isset($rates[$from]) || !isset($rates[$to])) {
				$error = true;
			} elseif (empty($rates[$from]) || empty($rates[$to])) {
				$error = true;
			}
	
			if ($error) {
				//				$this->log($this->__('Currency conversion error.'));
				if (isset($result[$output])) {
					return $result[$output];
				} else {
					return $result;
				}
			}
	
			$result = array(
					'price' => ($price * $rates[$to]) / $rates[$from],
					'currency' => $to
			);
		}
	
		if (is_int($round)) {
			$result['price'] = round($result['price'], $round);
		}
	
		if (isset($result[$output])) {
			return $result[$output];
		}
	
		return $result;
	}
	
	public function convertRateCurrency($price, $currentcurrency)
	{
		$baseCurrencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
		$result = array('price' => $price, 'currency' => $currentcurrency);
	
		if ($currentcurrency != $baseCurrencyCode) {
			$result = $this->currencyConvert($price, $currentcurrency , $baseCurrencyCode);
		}
		return $result;
	}
}
