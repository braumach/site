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
use Ced\Amazon\Model\Source\Strategy\Assignment\Category as AmazonCategory;

class TypeModifier implements ModifierInterface
{
    public $request;

    public $resource;
    public $globalStrategy;
    public $assignmentResource;
    public $assignment;

    public $name;
    public $config;
    public $category;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Ced\Amazon\Model\Source\Strategy\Name $name,
        \Ced\Amazon\Service\Config $config,
        \Ced\Amazon\Model\ResourceModel\Strategy\GlobalStrategy $resource,
        \Ced\Amazon\Model\Strategy\GlobalStrategy $globalStrategy,
        \Ced\Amazon\Model\ResourceModel\Strategy\Assignment $assignmentResource,
        \Ced\Amazon\Model\Strategy\Assignment $assignment,
        AmazonCategory $category
    ) {
        $this->request = $request;

        $this->name = $name;
        $this->config = $config;
        $this->resource = $resource;
        $this->globalStrategy = $globalStrategy;
        $this->assignmentResource = $assignmentResource;
        $this->assignment = $assignment;
        $this->category = $category;
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
        if (!empty($id) && $type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_GLOBAL) {
            $this->resource->load(
                $this->globalStrategy,
                $id,
                \Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_GLOBAL_RELATION_ID
            );
            $data[$id] = array_merge($this->globalStrategy->getData(), $data[$id]);
        } elseif (!empty($id) && $type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_ASSIGNMENT) {
            $this->assignmentResource->load(
                $this->assignment,
                $id,
                \Ced\Amazon\Model\Strategy\Assignment::COLUMN_ASSIGNMENT_RELATION_ID
            );
            $data[$id] = array_merge($this->assignment->getData(), $data[$id]);
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

        if ($type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_GLOBAL) {
            // Automatic rule based assignment
            if ($this->config->getStrategyAutoAssignment()) {
                $meta['general']['children'][\Ced\Amazon\Api\Data\StrategyInterface::COLUMN_NAME] = [
                    'arguments' => [
                        'data' => [
                            'options' => $this->name->toOptionArray(),
                            'config' => [
                                'formElement' => 'select',
                                'componentType' => 'field',
                                'visible' => 1,
                                'validation' => [
                                    'required-entry' => 1
                                ],
                                'label' => __('Strategy Name'),
                            ]
                        ]
                    ]
                ];
            }

            $meta['type'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Type Configuration [Global]'),
                            'sortOrder' => 50,
                            'collapsible' => true
                        ]
                    ]
                ],
                'children' => [
                    \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_FULFILLMENT_LATENCY => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => 'input',
                                    'componentType' => 'field',
                                    'visible' => 1,
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
                    \Ced\Amazon\Api\Data\Strategy\ShippingInterface::COLUMN_MERCHANT_SHIPPING_GROUP_NAME => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => 'input',
                                    'componentType' => 'field',
                                    'visible' => 1,
                                    'validation' => [
                                        'required-entry' => 1
                                    ],
                                    'label' => __('Merchant Shipping Group Name'),
                                    'additionalInfo' => ""
                                ]
                            ]
                        ]
                    ],
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
                ]
            ];
        } elseif ($type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_PRICE) {
            $meta['type'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Type Configuration [Price]'),
                            'sortOrder' => 50,
                            'collapsible' => true
                        ]
                    ]
                ],
            ];
        } elseif ($type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_SHIPPING) {
            $meta['type'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Type Configuration [Shipping]'),
                            'sortOrder' => 50,
                            'collapsible' => true
                        ]
                    ]
                ],
            ];
        } elseif ($type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_ASSIGNMENT) {
            $meta['type'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Type Configuration [Assignment]'),
                            'sortOrder' => 50,
                            'collapsible' => true
                        ]
                    ]
                ],
                'children' => [
                    \Ced\Amazon\Api\Data\Strategy\AssignmentInterface::COLUMN_CATEGORY_ID => [
                        'arguments' => [
                            'data' => [
                                'options' => $this->category->toOptionArray(),
                                'config' => [
                                    'formElement' => 'select',
                                    'component' => 'Magento_Ui/js/form/element/ui-select',
                                    'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                                    'componentType' => 'field',
                                    'breakLine' => 'true',
                                    'chipsEnabled' => 'true',
                                    'filterOptions' => 'true',
                                    'showCheckbox' => 'true',
                                    'disableLabel' => 'true',
                                    'multiple' => false,
                                    'visibleValue' => '3',
                                    'levelsVisibility' => '1',
                                    'required' => 1,
                                    'visible' => 1,
                                    'validation' => [
                                        'required-entry' => 1
                                    ],
                                    'label' => __('Auto Assign Category'),
                                    'additionalInfo' => "The selected Category product addition will be observed and assigned automatically to this profile on selection."
                                ]
                            ]
                        ]
                    ],
                ],
            ];
        }

        return $meta;
    }
}
