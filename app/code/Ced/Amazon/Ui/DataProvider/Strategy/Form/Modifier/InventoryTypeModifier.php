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

namespace Ced\Amazon\Ui\DataProvider\Strategy\Form\Modifier;

use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class InventoryTypeModifier implements ModifierInterface
{
    const CONTAINER_INVENTORY_THRESHOLD = 'inventory_threshold_container';
    const FIELD_IS_DELETE = 'is_delete';

    /** @var \Magento\Framework\App\RequestInterface */
    public $request;

    /** @var \Ced\Amazon\Model\ResourceModel\Strategy\Assignment */
    public $resource;

    /** @var \Ced\Amazon\Model\Strategy\Inventory */
    public $strategy;

    /** @var \Ced\Amazon\Helper\Config */
    public $config;

    /** @var \Ced\Amazon\Model\Source\Strategy\Attribute\Magento\Options */
    public $magento;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Ced\Amazon\Helper\Config $config,
        \Ced\Amazon\Model\ResourceModel\Strategy\Inventory $resource,
        \Ced\Amazon\Model\Strategy\Inventory $strategy,
        \Ced\Amazon\Model\Source\Strategy\Attribute\Magento\Options $magento
    ) {
        $this->request = $request;
        $this->magento = $magento;
        $this->config = $config;
        $this->resource = $resource;
        $this->strategy = $strategy;
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
        if (!empty($id) && $type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_INVENTORY) {
            $this->resource->load(
                $this->strategy,
                $id,
                \Ced\Amazon\Model\Strategy\Inventory::COLUMN_INVENTORY_RELATION_ID
            );
            $data[$id] = array_merge($this->strategy->getData(), $data[$id]);
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

        if ($type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_INVENTORY) {
            $meta['type'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Type Configuration [Inventory]'),
                            'sortOrder' => 50,
                            'collapsible' => true
                        ]
                    ]
                ],
                'children' => [
                    \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_INVENTORY_TYPE => [
                        'arguments' => [
                            'data' => [
                                'options' => [
                                    [
                                        'value' => \Ced\Amazon\Api\Data\Strategy\InventoryInterface::INVENTORY_TYPE_QUANTITY,
                                        'label' => __('Quantity'),
                                    ],
                                    [
                                        'value' => \Ced\Amazon\Api\Data\Strategy\InventoryInterface::INVENTORY_TYPE_AVAILABLE,
                                        'label' => __('Available'),
                                    ],
                                    [
                                        'value' => \Ced\Amazon\Api\Data\Strategy\InventoryInterface::INVENTORY_TYPE_LOOKUP,
                                        'label' => __('Lookup'),
                                    ]
                                ],
                                'config' => [
                                    'component' => 'Ced_Amazon/js/strategy/inventory/type',
                                    'dataType' => Element\DataType\Text::NAME,
                                    'formElement' => Element\Select::NAME,
                                    'componentType' => Field::NAME,
                                    'disabled' => false,
                                    'visible' => true,
                                    'label' => __("Inventory Type"),
                                    'sortOrder' => 10,
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'default' => \Ced\Amazon\Api\Data\Strategy\InventoryInterface::INVENTORY_TYPE_QUANTITY,
                                    'additionalInfo' => "Choose inventory type to for syncing:<br>" .
                                        "<li><b>Quantity</b>: For unit quantity available in store</li>" .
                                        "<li><b>Lookup</b>: For quantity sync from Fulfillment Network</li>" .
                                        "<li><b>Available</b>: For static quantity, accepted boolean value:" .
                                        " '<b>true</b>' or '<b>false</b>'.</li>",
                                ]
                            ]
                        ]
                    ],

                    \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_FULFILLMENT_CENTER_ID_ATTRIBUTE => [
                        'arguments' => [
                            'data' => [
                                'options' => $this->magento->getAllOptions(),
                                'config' => [
                                    'component' => 'Ced_Amazon/js/strategy/inventory/fulfillment',
                                    'dataType' => Element\DataType\Text::NAME,
                                    'formElement' => Element\Select::NAME,
                                    'componentType' => Field::NAME,
                                    'disabled' => false,
                                    'visible' => true,
                                    'label' => __("FulFillment Center Id (Attribute)"),
                                    'sortOrder' => 20,
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'additionalInfo' => "Provide a Magento Product Attribute to Fetch the Fulfillment Center Id"
                                ]
                            ]
                        ]
                    ],
                    \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_FULFILLMENT_CENTER_ID => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => Element\DataType\Text::NAME,
                                    'formElement' => Element\Input::NAME,
                                    'componentType' => Field::NAME,
                                    'disabled' => false,
                                    'visible' => true,
                                    'label' => __("Fulfillment Center Id Value"),
                                    'sortOrder' => 25,
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'additionalInfo' => "Default value for the Fulfillment Center Id Value"
                                ]
                            ]
                        ]
                    ],
                    \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_AVAILABLE_ATTRIBUTE => [
                        'arguments' => [
                            'data' => [
                                'options' => $this->magento->getAllOptions(),
                                'config' => [
                                    'component' => 'Ced_Amazon/js/strategy/inventory/available',
                                    'dataType' => Element\DataType\Text::NAME,
                                    'formElement' => Element\Select::NAME,
                                    'componentType' => Field::NAME,
                                    'disabled' => false,
                                    'visible' => true,
                                    'label' => __("Available (Attribute)"),
                                    'sortOrder' => 30,
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'additionalInfo' => "Provide a Magento Product Attribute to Fetch the Available Boolean Value"                                ]
                            ]
                        ]
                    ],
                    \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_AVAILABLE => [
                        'arguments' => [
                            'data' => [
                                'options' => [
                                    [
                                        'value' => "true",
                                        'label' => __('true'),
                                    ],
                                    [
                                        'value' => "false",
                                        'label' => __('false'),
                                    ],
                                ],
                                'config' => [
                                    'dataType' => Element\DataType\Text::NAME,
                                    'formElement' => Element\Select::NAME,
                                    'componentType' => Field::NAME,
                                    'disabled' => false,
                                    'visible' => true,
                                    'label' => __("Available Value"),
                                    'sortOrder' => 35,
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'additionalInfo' => "Default value for the Available Value"
                                ]
                            ]
                        ]
                    ],
                    \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_QUANTITY_ATTRIBUTE => [
                        'arguments' => [
                            'data' => [
                                'options' => $this->magento->getAllOptions(),
                                'config' => [
                                    'component' => 'Ced_Amazon/js/strategy/inventory/quantity',
                                    'dataType' => Element\DataType\Text::NAME,
                                    'formElement' => Element\Select::NAME,
                                    'componentType' => Field::NAME,
                                    'disabled' => false,
                                    'visible' => true,
                                    'label' => __("Quantity (Attribute)"),
                                    'sortOrder' => 40,
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'default' => 'quantity_and_stock_status',
                                    'additionalInfo' => "Provide a Magento Product Attribute to Fetch the Quantity Numeric Value"                                ]
                            ]
                        ]
                    ],
                    \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_QUANTITY => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => Element\DataType\Number::NAME,
                                    'formElement' => Element\Input::NAME,
                                    'componentType' => Field::NAME,
                                    'disabled' => false,
                                    'visible' => true,
                                    'label' => __("Quantity Value"),
                                    'sortOrder' => 45,
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'additionalInfo' => "Default value for the Quantity Value"
                                ]
                            ]
                        ]
                    ],

                    \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_FULFILLMENT_LATENCY => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => Element\DataType\Text::NAME,
                                    'formElement' => Element\Input::NAME,
                                    'componentType' => Field::NAME,
                                    'visible' => 1,
                                    'sortOrder' => 100,
                                    'validation' => [
                                        'required-entry' => 1
                                    ],
                                    'default' => '1',
                                    'label' => __('Fulfillment Latency (Handling Time)'),
                                    'additionalInfo' => "The number of days between the order date and the shipment creation date (a whole number between <b>1 and 30</b> )."
                                ]
                            ]
                        ]
                    ],

                    \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_INVENTORY_OVERRIDE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => 'checkbox',
                                    'prefer' => 'toggle',
                                    'componentType' => 'field',
                                    'valueMap' => [
                                        "true" => 1,
                                        "false" => 0
                                    ],
                                    'sortOrder' => 200,
                                    'default' => 0,
                                    'visible' => 1,
                                    'validation' => [
                                        'required-entry' => 1
                                    ],
                                    'label' => __('Inventory Override'),
                                    'additionalInfo' => "Choose yes to override inventory for syncing. <br>" .
                                        "Magento inventory will be taken from the product qty field, irrespective of " .
                                        "any rules or condition set (like 'Manage Stock' is No)",
                                    "component" => "Magento_Ui/js/form/element/single-checkbox",
                                ]
                            ]
                        ]
                    ],

                    self::CONTAINER_INVENTORY_THRESHOLD => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Threshold Configuration'),
                                    'sortOrder' => 1000,
                                    'collapsible' => true,
                                    'componentType' => Fieldset::NAME,
                                ]
                            ]
                        ],
                        'children' => $this->getThresholdFields()
                    ],
                ]
            ];
        }

        return $meta;
    }

    private function getThresholdFields()
    {
        $meta = [
            \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'checkbox',
                            'prefer' => 'toggle',
                            'componentType' => 'field',
                            'valueMap' => [
                                "true" => 1,
                                "false" => 0
                            ],
                            'default' => 0,
                            'visible' => 1,
                            'validation' => [
                                'required-entry' => 1
                            ],
                            'label' => __('Inventory Threshold'),
                            'additionalInfo' => "Enable to send inventory on Amazon by threshold conditions.",
                            "component" => "Ced_Amazon/js/strategy/global/threshold",
                        ]
                    ]
                ]
            ],
            \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD_BREAKPOINT => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Element\DataType\Number::NAME,
                            'formElement' => 'input',
                            'componentType' => 'field',
                            'visible' => 1,
                            'validation' => [
                                'required-entry' => 1,
                                'validate-greater-than-zero' => 1,
                            ],
                            'label' => __('Inventory Threshold Breakpoint'),
                            "additionalInfo" => "Set a breakpoint inventory quantity on which lesser and greater condition will act upon."
                        ]
                    ]
                ]
            ],
            \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD_GREATER_THAN_VALUE => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'input',
                            'componentType' => 'field',
                            'dataType' => Element\DataType\Number::NAME,
                            'visible' => 1,
                            'validation' => [
                                'validate-not-negative-number' => 1,
                            ],
                            'label' => __('Inventory Threshold Greater Than Value'),
                            'additionalInfo' => "Send Quantity to Amazon for those products, whose inventory is <b>GREATER</b> than the inventory threshold breakpoint."
                        ]
                    ]
                ]
            ],
            \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD_LESS_THAN_VALUE => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'input',
                            'dataType' => Element\DataType\Number::NAME,
                            'componentType' => 'field',
                            'visible' => 1,
                            'validation' => [
                                'validate-not-negative-number' => 1,
                            ],
                            'label' => __('Inventory Threshold Less Than Value'),
                            'additionalInfo' => "Send Quantity to Amazon for those products, whose inventory is <b>LESS</b> than or equal to the inventory threshold breakpoint."
                        ]
                    ]
                ]
            ],
        ];

        return $meta;
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
                        \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_ADDITIONAL_ATTRIBUTES => $this->getUnitsRows(10),
                    ]
                ]
            ],
        ];

        return $meta;
    }

    private function getMarketplaceRows($sortOrder)
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
                                        'label' => __("Default"),
                                        'sortOrder' => 30,
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
}
