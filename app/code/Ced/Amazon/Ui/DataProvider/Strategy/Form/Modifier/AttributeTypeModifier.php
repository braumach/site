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
 * @package     Ced_2.3
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\DataProvider\Strategy\Form\Modifier;

use Ced\Amazon\Model\ResourceModel\Strategy\Attribute as AttributeStrategyResource;
use Ced\Amazon\Model\Source\Strategy\Assignment\Category as AmazonCategory;
use Ced\Amazon\Model\Source\Strategy\Attribute\Magento\Options as MagentoAttributeList;
use Ced\Amazon\Model\Source\Strategy\Attribute\Options as AmazonAttributeList;
use Ced\Amazon\Model\Source\Strategy\Attribute\Units as AmazonAttributeUnits;
use Ced\Amazon\Model\Strategy\Attribute as AttributeStrategyModel;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Ced\Amazon\Model\Source\Profile\Category as AmazonCategoryList;

class AttributeTypeModifier implements ModifierInterface
{
    const CONTAINER_ATTRIBUTE_NAME = 'additional_attributes_container';
    const FIELD_IS_DELETE = 'is_delete';

    public $request;
    public $attributeResource;
    public $attribute;
    public $attributes;
    public $units;
    public $magento;
    public $category;
    public $type;

    public function __construct(
        RequestInterface $request,
        AttributeStrategyResource $attributeResource,
        AttributeStrategyModel $attribute,
        AmazonAttributeList $attributes,
        AmazonAttributeUnits $units,
        AmazonCategory $category,
        MagentoAttributeList $magento,
        AmazonCategoryList $type
    ) {
        $this->request = $request;
        $this->attributes = $attributes;
        $this->units = $units;
        $this->magento = $magento;
        $this->category = $category;
        $this->attributeResource = $attributeResource;
        $this->attribute = $attribute;
        $this->type = $type;
    }

    /**
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    public function modifyData(array $data)
    {
        $id = $this->request->getParam('id');
        $type = $this->request->getParam('type');
        if (!empty($id) && $type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_ATTRIBUTE) {
            $this->loadStrategyModel($id);
            $data[$id] = array_merge($this->attribute->getData(), $data[$id]);
            $type = $this->attribute->getData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_PRODUCT_TYPE);
            $subtype = $this->attribute->getData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_PRODUCT_SUB_TYPE);
            if (!empty($subtype) && !empty($type)) {
                $data[$id][\Ced\Amazon\Model\Strategy\Attribute::COLUMN_PRODUCT_SUB_TYPE] = $type . "_" . $subtype;
            }

            $additional = [];
            if (isset($data[$id][\Ced\Amazon\Model\Strategy\Attribute::COLUMN_ADDITIONAL_ATTRIBUTES])) {
                $additional = json_decode(
                    $data[$id][\Ced\Amazon\Model\Strategy\Attribute::COLUMN_ADDITIONAL_ATTRIBUTES],
                    true
                ) ?: [];
            }
            $data[$id][\Ced\Amazon\Model\Strategy\Attribute::COLUMN_ADDITIONAL_ATTRIBUTES] = $additional;

            $units = [];
            if (isset($data[$id][\Ced\Amazon\Model\Strategy\Attribute::COLUMN_UNITS])) {
                $units = json_decode(
                    $data[$id][\Ced\Amazon\Model\Strategy\Attribute::COLUMN_UNITS],
                    true
                ) ?: [];
            }
            $data[$id][\Ced\Amazon\Model\Strategy\Attribute::COLUMN_UNITS] = $units;
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    public function modifyMeta(array $meta)
    {
        $id = $this->request->getParam('id');
        $type = $this->request->getParam('type', 'global_strategy');
        if (!in_array($type, \Ced\Amazon\Model\Source\Strategy\Type::AVAILABLE_STRATEGIES)) {
            $type = 'global_strategy';
        }

        if (empty($id) && in_array($type, \Ced\Amazon\Model\Source\Strategy\Type::AVAILABLE_STRATEGIES)) {
            $meta['general']['children']
            [\Ced\Amazon\Api\Data\StrategyInterface::COLUMN_TYPE]['arguments']['data']['config']['default'] = $type;
        }

        if ($type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_ATTRIBUTE) {
            if (!empty($id)) {
                $this->loadStrategyModel($id);
                // Setting category to load attributes
                $this->attributes->setCategory(
                    $this->attribute->getData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_PRODUCT_TYPE)
                );
                $this->attributes->setSubCategory(
                    $this->attribute->getData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_PRODUCT_SUB_TYPE)
                );

                // Setting category to load units
                $this->units->setCategory(
                    $this->attribute->getData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_PRODUCT_TYPE)
                );
                $this->units->setSubCategory(
                    $this->attribute->getData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_PRODUCT_SUB_TYPE)
                );
            }

            $meta['type'] = $this->addAttributeType();
        }
        return $meta;
    }

    private function loadStrategyModel($id)
    {
        if (!empty($id) && $this->attribute->getId() != $id) {
            $this->attributeResource->load(
                $this->attribute,
                $id,
                \Ced\Amazon\Model\Strategy\Attribute::COLUMN_ATTRIBUTE_RELATION_ID
            );
        }
    }

    private function addAttributeType()
    {
        $meta = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Type Configuration [Attribute]'),
                        'sortOrder' => 50,
                        'collapsible' => true
                    ]
                ]
            ],
            'children' => [
                \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_SKU => [
                    'arguments' => [
                        'data' => [
                            'options' => $this->magento->getAllOptions(),
                            'config' => [
                                'dataType' => Element\DataType\Text::NAME,
                                'formElement' => Element\Select::NAME,
                                'componentType' => Field::NAME,
                                'disabled' => false,
                                'visible' => true,
                                'label' => __("SKU"),
                                'sortOrder' => 10,
                                'validation' => [
                                    'required-entry' => true
                                ],
                                'default' => 'sku'
                            ]
                        ]
                    ]
                ],
                \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_UPC => [
                    'arguments' => [
                        'data' => [
                            'options' => $this->magento->getAllOptions(),
                            'config' => [
                                'dataType' => Element\DataType\Text::NAME,
                                'formElement' => Element\Select::NAME,
                                'componentType' => Field::NAME,
                                'disabled' => false,
                                'visible' => true,
                                'label' => __("UPC"),
                                'sortOrder' => 20,
                                'validation' => [
                                    'required-entry' => false
                                ],
                                'default' => 'upc'
                            ]
                        ]
                    ]
                ],
                \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_EAN => [
                    'arguments' => [
                        'data' => [
                            'options' => $this->magento->getAllOptions(),
                            'config' => [
                                'dataType' => Element\DataType\Text::NAME,
                                'formElement' => Element\Select::NAME,
                                'componentType' => Field::NAME,
                                'disabled' => false,
                                'visible' => true,
                                'label' => __("EAN"),
                                'sortOrder' => 30,
                                'validation' => [
                                    'required-entry' => false
                                ],
                                'default' => 'ean'
                            ]
                        ]
                    ]
                ],
                \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_GTIN => [
                    'arguments' => [
                        'data' => [
                            'options' => $this->magento->getAllOptions(),
                            'config' => [
                                'dataType' => Element\DataType\Text::NAME,
                                'formElement' => Element\Select::NAME,
                                'componentType' => Field::NAME,
                                'disabled' => false,
                                'visible' => true,
                                'label' => __("GTIN"),
                                'sortOrder' => 40,
                                'validation' => [
                                    'required-entry' => false
                                ],
                                'default' => 'gtin'
                            ]
                        ]
                    ]
                ],
                \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_ASIN => [
                    'arguments' => [
                        'data' => [
                            'options' => $this->magento->getAllOptions(),
                            'config' => [
                                'dataType' => Element\DataType\Text::NAME,
                                'formElement' => Element\Select::NAME,
                                'componentType' => Field::NAME,
                                'disabled' => false,
                                'visible' => true,
                                'label' => __("ASIN"),
                                'sortOrder' => 50,
                                'validation' => [
                                    'required-entry' => false
                                ],
                                'default' => 'asin'
                            ]
                        ]
                    ]
                ],
                \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_TITLE => [
                    'arguments' => [
                        'data' => [
                            'options' => $this->magento->getAllOptions(),
                            'config' => [
                                'dataType' => Element\DataType\Text::NAME,
                                'formElement' => Element\Select::NAME,
                                'componentType' => Field::NAME,
                                'disabled' => false,
                                'visible' => true,
                                'label' => __("Title"),
                                'sortOrder' => 60,
                                'validation' => [
                                    'required-entry' => true
                                ],
                                'default' => 'name'
                            ]
                        ]
                    ]
                ],
                \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_DESCRIPTION => [
                    'arguments' => [
                        'data' => [
                            'options' => $this->magento->getAllOptions(),
                            'config' => [
                                'dataType' => Element\DataType\Text::NAME,
                                'formElement' => Element\Select::NAME,
                                'componentType' => Field::NAME,
                                'disabled' => false,
                                'visible' => true,
                                'label' => __("Description"),
                                'sortOrder' => 70,
                                'validation' => [
                                    'required-entry' => true
                                ],
                                'default' => 'description'
                            ]
                        ]
                    ]
                ],
                \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_BRAND => [
                    'arguments' => [
                        'data' => [
                            'options' => $this->magento->getAllOptions(),
                            'config' => [
                                'dataType' => Element\DataType\Text::NAME,
                                'formElement' => Element\Select::NAME,
                                'componentType' => Field::NAME,
                                'disabled' => false,
                                'visible' => true,
                                'label' => __("Brand"),
                                'sortOrder' => 80,
                                'validation' => [
                                    'required-entry' => true
                                ],
                                'default' => 'brand'
                            ]
                        ]
                    ]
                ],
                \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_MANUFACTURER => [
                    'arguments' => [
                        'data' => [
                            'options' => $this->magento->getAllOptions(),
                            'config' => [
                                'dataType' => Element\DataType\Text::NAME,
                                'formElement' => Element\Select::NAME,
                                'componentType' => Field::NAME,
                                'disabled' => false,
                                'visible' => true,
                                'label' => __("Manufaturer"),
                                'sortOrder' => 90,
                                'validation' => [
                                    'required-entry' => false
                                ],
                                'default' => 'manufacturer'
                            ]
                        ]
                    ]
                ],
                \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_PRODUCT_SUB_TYPE => [
                    'arguments' => [
                        'data' => [
                            'options' => $this->type->toOptionArray(),
                            'config' => [
                                'formElement' => 'select',
                                'component' => 'Ced_Amazon/js/strategy/attribute/product_type',
                                'elementTmpl' => 'Ced_Amazon/grid/filters/elements/ui-select',
                                'componentType' => 'field',
                                'source' => 'item',
                                'breakLine' => 'true',
                                'filterOptions' => 'true',
                                'showCheckbox' => 'true',
                                'disableLabel' => 'true',
                                'multiple' => 'true',
                                'visibleValue' => '3',
                                'levelsVisibility' => '2',
                                'required' => 1,
                                'visible' => 1,
                                'validation' => [
                                    'required-entry' => 1
                                ],
                                'listens' => [
                                    '${ $.namespace }.${ $.namespace }:responseData' => 'setParsed'
                                ],
                                'default' => 'DefaultCatergory_DefaultCatergory',
                                'label' => __('Product Type'),
                                'additionalInfo' => '<ul><li>The Product Type acts as an <b>"Attribute Set"</b>
                         to render the <u>required</u>, <u>recommended</u> and <u>optional</u> attributes and
                          should be used for product upload.</li><li>Use <b>"Default Category"</b> for syncing the
                           product inventory and price only.</li></ul>'
                            ]
                        ]
                    ]
                ],
                "additional_attributes_container" => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Attributes'),
                                'sortOrder' => 1000,
                                'collapsible' => true,
                                'componentType' => Fieldset::NAME,
                            ]
                        ]
                    ],
                    'children' => [
                        \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_ADDITIONAL_ATTRIBUTES => $this->getAttributeRows(10),
                    ]
                ],
                "units_container" => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Units'),
                                'sortOrder' => 2000,
                                'collapsible' => true,
                                'componentType' => Fieldset::NAME,
                            ]
                        ]
                    ],
                    'children' => [
                        \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_UNITS => $this->getUnitsRows(10),
                    ]
                ]
            ],
        ];

        return $meta;
    }

    private function getAttributeRows($sortOrder)
    {
        $rows = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Attribute'),
                        'componentType' => DynamicRows::NAME,
                        'component' => "Magento_Ui/js/dynamic-rows/dynamic-rows",
                        'template' => "ui/dynamic-rows/templates/default",
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'dndConfig' => [
                            'enabled' => false
                        ],
                        'deleteValue' => true,
                        'visible' => true,
                        'addButton' => true,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        \Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_ID => [
                            'arguments' => [
                                'data' => [
                                    'options' => $this->attributes->getAllOptions(),
                                    'config' => [
                                        'component' => 'Ced_Amazon/js/strategy/attribute/amazon',
                                        'formElement' => Element\Select::NAME,
                                        'componentType' => Field::NAME,
                                        'disabled' => false,
                                        'visible' => true,
                                        'label' => __("Attribute Id"),
                                        'sortOrder' => 10,
                                        'dataType' => Element\DataType\Text::NAME,
                                        'validation' => [
                                            'required-entry' => true
                                        ],
                                        'tooltipTpl' => "Ced_Amazon/form/element/helper/tooltip",
                                        'tooltip' => [
                                            'description' => ""
                                        ]
                                    ],
                                ],

                            ]
                        ],
                        \Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_MAGENTO_CODE => [
                            'arguments' => [
                                'data' => [
                                    'options' => $this->magento->getAllOptions(),
                                    'config' => [
                                        'dataType' => Element\DataType\Text::NAME,
                                        'formElement' => Element\Select::NAME,
                                        'componentType' => Field::NAME,
                                        'disabled' => false,
                                        'visible' => true,
                                        'label' => __("Magento Attribute"),
                                        'sortOrder' => 20,
                                        'validation' => [
                                            'required-entry' => true
                                        ]
                                    ],
                                ],
                            ]
                        ],
                        \Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_DEFAULT_VALUE => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Element\Input::NAME,
                                        'dataType' => Element\DataType\Text::NAME,
                                        'componentType' => Field::NAME,
                                        'disabled' => false,
                                        'visible' => true,
                                        'label' => __("Default Value"),
                                        'sortOrder' => 30,
                                        'dataScope' => 'default_value',
                                        'validation' => [
                                            'required-entry' => false
                                        ]
                                    ],
                                ],
                            ]
                        ],
                        'default_value_select' => [
                            'arguments' => [
                                'data' => [
                                    'options' => [],
                                    'config' => [
                                        'formElement' => Element\Select::NAME,
                                        'dataType' => Element\DataType\Text::NAME,
                                        'componentType' => Field::NAME,
                                        'disabled' => false,
                                        'visible' => false,
                                        'label' => __("Default Value"),
                                        'sortOrder' => 30,
                                        'dataScope' => 'default_value_select',
                                        'validation' => [
                                            'required-entry' => false
                                        ]
                                    ],
                                ],
                            ]
                        ],
                        Element\ActionDelete::NAME => [
                            'arguments' => [
                                'data' => [
                                    'options' => [],
                                    'config' => [
                                        'formElement' => Element\Select::NAME,
                                        'dataType' => Element\DataType\Text::NAME,
                                        'componentType' => Element\ActionDelete::NAME,
                                        'disabled' => false,
                                        'visible' => true,
                                        'label' => __("Actions"),
                                        'sortOrder' => 40,
                                        'additionalClasses' => [
                                            "data-grid-actions-cell"
                                        ]
                                    ],
                                ],
                            ]
                        ],
                    ]
                ]
            ]
        ];

        return $rows;
    }

    private function getUnitsRows($sortOrder)
    {
        $rows = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Unit'),
                        'componentType' => DynamicRows::NAME,
                        'component' => "Magento_Ui/js/dynamic-rows/dynamic-rows",
                        'template' => "ui/dynamic-rows/templates/default",
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'dndConfig' => [
                            'enabled' => false
                        ],
                        'deleteValue' => true,
                        'visible' => true,
                        'addButton' => true,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        \Ced\Amazon\Api\Data\UnitInterface::ATTRIBUTE_ID => [
                            'arguments' => [
                                'data' => [
                                    'options' => $this->units->getAllOptions(),
                                    'config' => [
                                        'formElement' => Element\Select::NAME,
                                        'componentType' => Field::NAME,
                                        'disabled' => false,
                                        'visible' => true,
                                        'label' => __("Unit Id"),
                                        'sortOrder' => 10,
                                        'dataType' => Element\DataType\Text::NAME,
                                        'validation' => [
                                            'required-entry' => true
                                        ],
                                        'tooltip' => [
                                            'description' => ""
                                        ]
                                    ],
                                ],

                            ]
                        ],
                        \Ced\Amazon\Api\Data\UnitInterface::ATTRIBUTE_VALUE => [
                            'arguments' => [
                                'data' => [
                                    'options' => $this->units->getUnitValueOptions(),
                                    'config' => [
                                        'dataType' => Element\DataType\Text::NAME,
                                        'formElement' => Element\Select::NAME,
                                        'componentType' => Field::NAME,
                                        'disabled' => false,
                                        'visible' => true,
                                        'label' => __("Unit Value"),
                                        'sortOrder' => 20,
                                        'validation' => [
                                            'required-entry' => true
                                        ]
                                    ],
                                ],
                            ]
                        ],
                        Element\ActionDelete::NAME => [
                            'arguments' => [
                                'data' => [
                                    'options' => [],
                                    'config' => [
                                        'formElement' => Element\Select::NAME,
                                        'dataType' => Element\DataType\Text::NAME,
                                        'componentType' => Element\ActionDelete::NAME,
                                        'disabled' => false,
                                        'visible' => true,
                                        'label' => __("Actions"),
                                        'sortOrder' => 30,
                                        'additionalClasses' => [
                                            "data-grid-actions-cell"
                                        ]
                                    ],
                                ],
                            ]
                        ],
                    ]
                ]
            ]
        ];

        return $rows;
    }
}
