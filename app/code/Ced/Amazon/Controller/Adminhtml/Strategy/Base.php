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

namespace Ced\Amazon\Controller\Adminhtml\Strategy;

/**
 * Class Save
 *
 * @package Ced\Amazon\Controller\Adminhtml\Strategy
 */
abstract class Base extends \Magento\Backend\App\Action
{
    public static $fields = [
        \Ced\Amazon\Api\Data\StrategyInterface::COLUMN_NAME => 'Strategy Name',
        \Ced\Amazon\Api\Data\StrategyInterface::COLUMN_TYPE => 'Strategy Type',
        \Ced\Amazon\Api\Data\StrategyInterface::COLUMN_ACTIVE => 'Strategy Active',
    ];

    /** @var array */
    public $invalid = [];

    /** @var \Ced\Amazon\Helper\Logger  */
    public $logger;

    /** @var \Ced\Amazon\Api\Data\StrategyInterface  */
    public $strategy;

    /** @var \Ced\Amazon\Repository\Strategy  */
    public $repository;

    /** @var \Ced\Amazon\Model\Strategy\GlobalStrategy  */
    public $globalStrategy;

    /** @var \Ced\Amazon\Model\ResourceModel\Strategy\GlobalStrategy  */
    public $globalStrategyResource;

    /** @var \Ced\Amazon\Model\Strategy\Attribute  */
    public $attributeStrategy;

    /** @var \Ced\Amazon\Model\ResourceModel\Strategy\Attribute\  */
    public $attributeStrategyResource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\Amazon\Repository\Strategy $repository,
        \Ced\Amazon\Model\ResourceModel\Strategy\GlobalStrategy $globalStrategyResource,
        \Ced\Amazon\Model\ResourceModel\Strategy\Attribute $attributeStrategyResource,
        \Ced\Amazon\Model\Strategy\GlobalStrategy $globalStrategy,
        \Ced\Amazon\Model\Strategy\Attribute $attributeStrategy,
        \Ced\Amazon\Model\Strategy $strategy,
        \Ced\Amazon\Helper\Logger $logger
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->strategy = $strategy;

        $this->globalStrategyResource = $globalStrategyResource;
        $this->globalStrategy = $globalStrategy;

        $this->attributeStrategyResource = $attributeStrategyResource;
        $this->attributeStrategy = $attributeStrategy;

        $this->logger = $logger;
    }

    public function validate()
    {
        $id = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy::COLUMN_ID);
        $name = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy::COLUMN_NAME);
        $type = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy::COLUMN_TYPE);
        $active = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy::COLUMN_ACTIVE);

        // Validating required.
        foreach (self::$fields as $field => $fieldName) {
            if (empty($this->getRequest()->getParam($field))) {
                $this->invalid[] = $fieldName;
            }
        }

        if (empty($this->invalid)) {
            $this->type($type, $id);

            if (!empty($id)) {
                $this->repository->load($this->strategy, $id);
            }

            $this->strategy
                ->setData(\Ced\Amazon\Model\Strategy::COLUMN_ACTIVE, $active)
                ->setData(\Ced\Amazon\Model\Strategy::COLUMN_NAME, $name)
                ->setData(\Ced\Amazon\Model\Strategy::COLUMN_TYPE, $type);

            return true;
        }

        return false;
    }

    public function type($type, $id)
    {
        if ($type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_GLOBAL) {
            $shipping = $this->getRequest()
                ->getParam(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_MERCHANT_SHIPPING_GROUP_NAME);
            $latency = $this->getRequest()
                ->getParam(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_FULFILLMENT_LATENCY);
            $threshold = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD);
            $breakpoint = $this->getRequest()
                ->getParam(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD_BREAKPOINT);
            $greater = $this->getRequest()
                ->getParam(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD_GREATER_THAN_VALUE);
            $less = $this->getRequest()
                ->getParam(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD_LESS_THAN_VALUE);

            if (!empty($id)) {
                $this->globalStrategyResource->load(
                    $this->globalStrategy,
                    $id,
                    \Ced\Amazon\Api\Data\Strategy\GlobalStrategyInterface::COLUMN_GLOBAL_RELATION_ID
                );
            }

            $this->globalStrategy
                ->setData(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_MERCHANT_SHIPPING_GROUP_NAME, $shipping);
            $this->globalStrategy
                ->setData(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_FULFILLMENT_LATENCY, $latency);
            $this->globalStrategy
                ->setData(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD, $threshold);
            $this->globalStrategy
                ->setData(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD_BREAKPOINT, $breakpoint);
            $this->globalStrategy
                ->setData(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD_GREATER_THAN_VALUE, $greater);
            $this->globalStrategy
                ->setData(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_THRESHOLD_LESS_THAN_VALUE, $less);
        } elseif ($type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_ATTRIBUTE) {
            $sku = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_SKU);
            $upc = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_UPC);
            $asin = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_ASIN);
            $ean = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_EAN);
            $gtin = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_GTIN);
            $description = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_DESCRIPTION);
            $brand = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_BRAND);
            $title = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_TITLE);
            $manufacturer = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_MANUFACTURER);
            $model = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_MODEL);

            $type = null;
            $subtype = $this->getRequest()->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_PRODUCT_SUB_TYPE, []);
            if (!empty($subtype)) {
                $result = explode('_', $subtype);
                if (isset($result[1])) {
                    $type = $result[1];
                }

                if (isset($result[0])) {
                    $subtype = $result[0];
                }
            }

            if (!empty($id)) {
                $this->attributeStrategyResource->load(
                    $this->attributeStrategy,
                    $id,
                    \Ced\Amazon\Api\Data\Strategy\AttributeInterface::COLUMN_ATTRIBUTE_RELATION_ID
                );
            }

            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_SKU, $sku);
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_UPC, $upc);
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_ASIN, $asin);
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_EAN, $ean);
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_GTIN, $gtin);
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_DESCRIPTION, $description);
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_BRAND, $brand);
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_TITLE, $title);
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_MANUFACTURER, $manufacturer);
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_MODEL, $model);
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_PRODUCT_TYPE, $type);
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_PRODUCT_SUB_TYPE, $subtype);

            $additionalAttributes = $this->getRequest()
                ->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_ADDITIONAL_ATTRIBUTES, []);
            if (!empty($additionalAttributes)) {
                $additionalAttributes = json_encode($additionalAttributes);
                $this->attributeStrategy->setData(
                    \Ced\Amazon\Model\Strategy\Attribute::COLUMN_ADDITIONAL_ATTRIBUTES,
                    $additionalAttributes
                );
            }

            $units = $this->getRequest()
                ->getParam(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_UNITS, []);
            if (!empty($units)) {
                $units = json_encode($units);
                $this->attributeStrategy
                    ->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_UNITS, $units);
            }
        }
    }

    public function save($type, $id)
    {
        if ($type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_GLOBAL) {
            $this->globalStrategy->setData(\Ced\Amazon\Model\Strategy\GlobalStrategy::COLUMN_GLOBAL_RELATION_ID, $id);
            $this->globalStrategyResource->save($this->globalStrategy);
        } elseif ($type == \Ced\Amazon\Model\Source\Strategy\Type::STRATEGY_ATTRIBUTE) {
            $this->attributeStrategy->setData(\Ced\Amazon\Model\Strategy\Attribute::COLUMN_ATTRIBUTE_RELATION_ID, $id);
            $this->attributeStrategyResource->save($this->attributeStrategy);
        }
    }
}
