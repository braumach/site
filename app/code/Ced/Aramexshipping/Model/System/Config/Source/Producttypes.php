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

namespace Ced\Aramexshipping\Model\System\Config\Source;

class Producttypes
{
    public function toOptionArray()
    {
        $options[] = array('value'=>'DPX', 'label'=>'Value Express Parcels');
		$options[] = array('value'=>'EDX', 'label'=>'Economy Document Express');
		$options[] = array('value'=>'EPX', 'label'=>'Economy Parcel Express');
		$options[] = array('value'=>'GDX', 'label'=>'Ground Document Express');
		$options[] = array('value'=>'GPX', 'label'=>'Ground Parcel Express');
		
		$options[] = array('value'=>'IBD', 'label'=>'International defered');
		$options[] = array('value'=>'PDX', 'label'=>'Priority Document Express');
		$options[] = array('value'=>'PLX', 'label'=>'Priority Letter Express (<.5 kg Docs)');
		$options[] = array('value'=>'PPX', 'label'=>'Priority Parcel Express');
		$options[] = array('value'=>'BLK', 'label'=>'Special: Bulk Mail Delivery');
		$options[] = array('value'=>'BLT', 'label'=>'Domestic - Bullet Delivery');
		$options[] = array('value'=>'CDA', 'label'=>'Special Delivery');
		$options[] = array('value'=>'CDS', 'label'=>'Special: Credit Cards Delivery');
		$options[] = array('value'=>'CGO', 'label'=>'Air Cargo (India)');
		
		$options[] = array('value'=>'COM', 'label'=>'Special: Cheque Collection');
		$options[] = array('value'=>'DEC', 'label'=>'Special: Invoice Delivery');
		$options[] = array('value'=>'EMD', 'label'=>'Early Morning delivery');
		$options[] = array('value'=>'FIX', 'label'=>'Special: Bank Branches Run');
		$options[] = array('value'=>'LGS', 'label'=>'Logistic Shipment');
		
		$options[] = array('value'=>'OND', 'label'=>'Overnight (Document)');
		$options[] = array('value'=>'ONP', 'label'=>'Overnight (Parcel)');
		$options[] = array('value'=>'P24', 'label'=>'Road Freight 24 hours service');
		$options[] = array('value'=>'P48', 'label'=>'Road Freight 48 hours service');
		$options[] = array('value'=>'PEC', 'label'=>'Economy Delivery');
		
		$options[] = array('value'=>'PEX', 'label'=>'Road Express');
		$options[] = array('value'=>'SFC', 'label'=>'Surface  Cargo (India)');
		$options[] = array('value'=>'SMD', 'label'=>'Same Day (Document)');
		$options[] = array('value'=>'SMP', 'label'=>'Same Day (Parcel)');
		$options[] = array('value'=>'SPD', 'label'=>'Special: Legal Branches Mail Service');
		
		$options[] = array('value'=>'SPL', 'label'=>'Special : Legal Notifications Delivery');
		
        return $options;
    }
    public function toKeyArray(){
    	$result  = array();
    	$options1 = $this->toOptionArray();
       	foreach($options1 as $option){
    		$result[$option['value']] = $option['label'];
    	}
    	return $result;
    }
}
