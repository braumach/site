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
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source\Strategy\Attribute;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\ObjectManagerInterface;

class Options extends AbstractSource
{
    const PRE_MAPPED_ATTRIBUTES = [
        "DescriptionData_Brand",
        "DescriptionData_Description",
        "DescriptionData_Title",
        "DescriptionData_Title",
        "SKU",
        "StandardProductID_Type",
        "StandardProductID_Value",
        "StandardProductID_Value_ASIN",
    ];

    public $objectManager;
    public $category = "DefaultCategory";
    public $subCategory = "DefaultCategory";

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getAllOptions()
    {
        $attributes = [];

        try {
            /**
             * @var \Amazon\Sdk\Product\CategoryInterface $category
             * @codingStandardsIgnoreStart
             */
            $category = $this->objectManager->create(
                '\Amazon\Sdk\Product\Category\\' . $this->category,
                [
                    'subCategory' => $this->subCategory
                ]
            );
            $attributes = $category->getAttributes();
            /** @codingStandardsIgnoreEnd */
        } catch (\Exception $e) {

        }

        $options = [
            "required" => [
                'value' => [],
                'label' => __("Required Attributes"),
            ],
            "optional" => [
                'value' => [],
                'label' => __("Optional Attributes"),
            ],
        ];

        foreach ($attributes as $id => $attribute) {
            if (!in_array($id, self::PRE_MAPPED_ATTRIBUTES)) {
                if (isset($attribute['minOccurs']) && $attribute['minOccurs'] != "0") {
                    $info = [
                        "default" => $this->getValue("default", $attribute),
                        "dataType" => $this->getValue("dataType", $attribute),
                    ];

                    $options["required"]["value"][] = [
                        'value' => $id,
                        'info' => $info,
                        'label' => __($attribute['name']),
                        'options' => isset($attribute['restriction']['optionValues']) ?
                            $attribute['restriction']['optionValues'] : [],
                    ];
                } else {
                    $info = [
                        "default" => $this->getValue("default", $attribute),
                        "dataType" => $this->getValue("dataType", $attribute),
                    ];

                    $options["optional"]["value"][] = [
                        'value' => $id,
                        'info' => $info,
                        'label' => __($attribute['name']),
                        'options' => isset($attribute['restriction']['optionValues']) ?
                            $attribute['restriction']['optionValues'] : [],
                    ];
                }
            }
        }

        if (isset($options["required"]["value"]) && empty($options["required"]["value"])) {
            unset($options["required"]);
        }

        return $options;
    }

    private function getValue($index, $haystack)
    {
        if (isset($haystack[$index])) {
            return $haystack[$index];
        }

        return null;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function setSubCategory($category)
    {
        $this->subCategory = $category;
    }
}
