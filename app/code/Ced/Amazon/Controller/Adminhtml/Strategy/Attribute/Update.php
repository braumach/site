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
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Strategy\Attribute;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action\Context;
use Ced\Amazon\Helper\Category;
use Ced\Amazon\Helper\Logger;
use Ced\Amazon\Repository\Strategy;

/**
 * Class Update
 * @package Ced\Amazon\Controller\Adminhtml\Strategy\Attribute
 */
class Update extends Action
{
    /** @var JsonFactory */
    public $jsonFactory;

    /** @var Strategy */
    public $strategy;

    /** @var Category */
    public $category;

    /** @var Logger */
    public $logger;

    /** @var array */
    public $initial = [];

    /**
     * Update constructor.
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Strategy $strategy
     * @param Category $category
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Strategy $strategy,
        Category $category,
        Logger $logger
    )
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->strategy = $strategy;
        $this->category = $category;
        $this->logger = $logger;
    }

    public function execute()
    {
        // If strategy is already saved then load the attributes and setting the same mapping in current mappings.
        $strategyId = $this->getRequest()->getParam('strategy_id');
        try {
            /** @var \Ced\Amazon\Api\Data\Strategy\AttributeInterface $strategy */
            $strategy = $this->strategy->getById($strategyId);
            $this->initial = $strategy->getAttributes();
        } catch (\Exception $e) {
            // ignore
        }

        $type = $this->getRequest()->getParam('product_type');
        $required = [];
        $optional = [];

        try {
            $type = explode('_', $type);
            if (isset($type[0], $type[1]) && !empty($type[0]) && !empty($type[1])) {
                $categoryId = $type[0];
                $subCategoryId = $type[1];

                $params = [
                    'minOccurs' => '1'
                ];
                /** @var array $required */
                $required = array_merge(
                    $required,
                    $this->category->getAttributes($categoryId, $subCategoryId, $params)
                );

                foreach (\Ced\Amazon\Model\Source\Strategy\Attribute\Options::PRE_MAPPED_ATTRIBUTES as $attributeId) {
                    if (isset($required[$attributeId])) {
                        unset($required[$attributeId]);
                    }
                }
                $required = $this->remap($required);

                $params = [
                    'minOccurs' => '0'
                ];
                /** @var array $optional */
                $optional = array_merge(
                    $optional,
                    $this->category->getAttributes($categoryId, $subCategoryId, $params)
                );

                $optional = $this->remap($optional);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
        }

        $attributes['required'] = $required;
        $attributes['optional'] = $optional;

        /** @var  $result */
        $result = $this->jsonFactory->create();
        $result->setData($attributes);
        return $result;
    }

    /**
     * Remapping the previous mapped values.
     * @param array $attributes
     * @return array
     */
    private function remap(array $attributes)
    {
        foreach ($attributes as $id => &$attribute) {
            if (isset(
                    $this->initial[$id],
                    $this->initial[$id][\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_MAGENTO_ATTRIBUTE_CODE]
                ) || isset(
                    $this->initial[$id],
                    $this->initial[$id][\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_DEFAULT_VALUE]
                )) {
                $attribute[\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_DEFAULT_VALUE] =
                    $this->initial[$id][\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_DEFAULT_VALUE];
                $attribute[\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_MAGENTO_ATTRIBUTE_CODE] =
                    $this->initial[$id][\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_MAGENTO_ATTRIBUTE_CODE];
            }
        }

        return $attributes;
    }
}
