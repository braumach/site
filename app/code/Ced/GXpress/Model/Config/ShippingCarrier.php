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

namespace Ced\GXpress\Model\Config;

use Ced\GXpress\Helper\GXpresslib;
use Ced\GXpress\Helper\Data;
use Ced\GXpress\Helper\MultiAccount;
use Magento\Framework\Option\ArrayInterface;

class ShippingCarrier implements ArrayInterface
{
    /**
     * @var Data
     */
    public $gxpressLibHelper;

    public $dataHelper;

    public $_coreRegistry;

    public $multiAccountHelper;

    /**
     * ShippingCarrier Constructor
     * @param GXpresslib $gxpressLibHelper
     * @param Data $dataHelper
     */
    public function __construct(
        GXpresslib $gxpressLibHelper,
        Data $dataHelper,
        \Magento\Framework\Registry $registry,
        MultiAccount $multiAccountHelper
    )

    {
        $this->gxpressLibHelper = $gxpressLibHelper;
        $this->dataHelper = $dataHelper;
        $this->_coreRegistry = $registry;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        /*$this->multiAccountHelper->getAccountRegistry(1);
        $shippingDetails = $this->gxpressLibHelper->getShipingCareer();*/
        $shippingDetails = '{
        "carriers": [
    {
        "name": "FedEx",
      "country": "US",
      "services": [
        "Ground",
        "Home Delivery",
        "Express Saver",
        "First Overnight",
        "Priority Overnight",
        "Standard Overnight",
        "2Day"
    ]
    },
    {
        "name": "UPS",
      "country": "US",
      "services": [
        "2nd Day Air",
        "2nd Day Air AM",
        "3 Day Select",
        "Ground",
        "Next Day Air",
        "Next Day Air Early AM",
        "Next Day Air Saver"
    ]
    },
    {
        "name": "USPS",
      "country": "US",
      "services": [
        "Priority Mail Express",
        "Media Mail",
        "Retail Ground",
        "Priority Mail",
        "First Class Mail"
    ]
    },
    {
        "name": "Australia Post",
      "country": "AU",
      "services": [
        "Regular Parcel",
        "Express Post"
    ]
    },
    {
        "name": "TNT",
      "country": "AU",
      "services": [
        "Road Express",
        "Overnight Express"
    ]
    },
    {
        "name": "TOLL",
      "country": "AU",
      "services": [
        "Road Delivery",
        "Overnight Priority"
    ]
    },
    {
        "name": "DHL",
      "country": "DE",
      "services": [
        "Paket",
        "Päckchen"
    ]
    },
    {
        "name": "DPD",
      "country": "DE",
      "services": [
        "Express 12",
        "Express",
        "Classic Parcel"
    ]
    },
    {
        "name": "Hermes",
      "country": "DE",
      "services": [
        "Päckchen",
        "Paketklasse S",
        "Paketklasse M",
        "Paketklasse L"
    ]
    },
    {
        "name": "UPS",
      "country": "DE",
      "services": [
        "Express",
        "Express Saver",
        "Standard"
    ]
    },
    {
        "name": "DHL UK",
      "country": "GB",
      "services": [
        "Express",
        "Express 12"
    ]
    },
    {
        "name": "DPD UK",
      "country": "GB",
      "services": [
        "Express 12",
        "Express Next Day",
        "Standard Parcel 12",
        "Standard Parcel Next Day",
        "Standard Parcel Two Day"
    ]
    },
    {
        "name": "RMG",
      "country": "GB",
      "services": [
        "1st Class Small Parcel",
        "1st Class Medium Parcel",
        "2nd Class Small Parcel",
        "2nd Class Medium Parcel"
    ]
    },
    {
        "name": "TNT UK",
      "country": "GB",
      "services": [
        "Express",
        "Express 10",
        "Express 12"
    ]
    },
    {
        "name": "UPS UK",
      "country": "GB",
      "services": [
        "Express",
        "Express Saver",
        "Standard"
    ]
    },
    {
        "name": "Yodel",
      "country": "GB",
      "services": [
        "B2C 48HR",
        "B2C 72HR",
        "B2C Packet"
    ]
    }
  ]
}';
        $shippingDetails = json_decode($shippingDetails,true);
        if (isset($shippingDetails['carriers'])) {
            foreach ($shippingDetails['carriers'] as $value) {
                if (isset($value['name'])) {
                    $options [] = [
                        'value' => $value['name'],
                        'label' => $value['name']
                    ];
                }
            }
        }
        return $options;
    }
}