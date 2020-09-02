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

class Units extends AbstractSource
{
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
        $units = [];

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
            $units = $category->getSubAttributes();
            /** @codingStandardsIgnoreEnd */
        } catch (\Exception $e) {
        }

        $options = [
            "required" => [
                'value' => [],
                'label' => __("Required Units"),
            ],
            "optional" => [
                'value' => [],
                'label' => __("Optional Units"),
            ],
        ];

        $required = [];
        $optional = [];
        foreach ($attributes as $id => $attribute) {
            if (isset($attribute['attribute'])) {
                if (isset($attribute['minOccurs']) && $attribute['minOccurs'] != "0") {
                    $required[$attribute['attribute']] = $attribute['name'];
                } else {
                    $optional[$attribute['attribute']] = $attribute['name'];
                }
            }
        }

        foreach ($units as $id => $unit) {
            $labelValues = explode("_", $id);
            $type = end($labelValues);

            if (isset($required[$id])) {
                $label = $required[$id] . " - " . $type;
                $options["required"]["value"][] = [
                    'value' => $id,
                    'type' => $type,
                    'label' => __($label),
                    'options' => isset($unit['restriction']['optionValues']) ?
                        $unit['restriction']['optionValues'] : [],
                ];
            } elseif (isset($optional[$id])) {
                $label = $optional[$id] . " - " . $type;
                $options["optional"]["value"][] = [
                    'value' => $id,
                    'type' => $type,
                    'label' => __($label),
                    'options' => isset($unit['restriction']['optionValues']) ?
                        $unit['restriction']['optionValues'] : [],
                ];
            }
        }

        if (isset($options["required"]["value"]) && empty($options["required"]["value"])) {
            unset($options["required"]);
        }

        return $options;
    }

    public function getUnitValueOptions()
    {
        $values = [];
        $options = [];
        $unitList = $this->getAllOptions();
        foreach ($unitList as $units) {
            foreach ($units['value'] as $i =>  $unit) {
                if (isset($unit['type'], $unit['options']) && !empty($unit['options'])) {
                    if (!isset($values[$unit['type']]['label'])) {
                        $values[$unit['type']]['label'] = $unit['type'];
                        $options[$i]['label'] = $unit['type'];
                    }

                    foreach ($unit['options'] as $value) {
                        if (!isset($values[$unit['type']]["value"][$value])) {
                            $values[$unit['type']]["value"][$value] = [
                                'value' => $value,
                                'label' => __($value),
                            ];
                            $options[$i]["value"][] = [
                                'value' => $value,
                                'label' => __($value),
                            ];
                        }
                    }
                }
            }
        }

        return $options;
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
