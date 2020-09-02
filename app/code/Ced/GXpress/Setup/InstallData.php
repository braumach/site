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

namespace Ced\GXpress\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

if (defined('DS') === false) define('DS', DIRECTORY_SEPARATOR);

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * @var EavSetupFactory
     */
    public $eavSetupFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public $eavAttribute;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\App\State $state
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\State $state
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->objectManager = $objectManager;
        $this->eavAttribute = $eavAttribute;
        $this->directoryList = $directoryList;
        $state->setAreaCode('frontend');
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $appPath = $this->directoryList->getRoot();

        $data = $objectManager->create('\Ced\GXpress\Helper\Data');
        $gxpressPath = $appPath . DS . "app" . DS . "code" . DS . "Ced" . DS . "GXpress" . DS . "Setup" . DS . "GXpressJson" . DS;

        $path = $gxpressPath . "gxpress_category.json";
        $categories = $data->loadFile($path);

        $path = $gxpressPath . "gxpress_attribute.json";
        $attributes = $data->loadFile($path);

        $path = $gxpressPath . "gxpress_confattributes.json";
        $confattributes = $data->loadFile($path);

        try {
            $table = $setup->getTable('gxpress_category');
            if($table) {
                $setup->getConnection()->truncateTable($table);
            }
            $setup->getConnection()->insertArray($table,
                [
                    'id',
                    'csv_firstlevel_id',
                    'csv_secondlevel_id',
                    'csv_thirdlevel_id',
                    'csv_fourthlevel_id',
                    'csv_fifthlevel_id',
                    'csv_sixthlevel_id',
                    'csv_seventhlevel_id',
                    'name',
                    'path',
                    'level',
                    'magento_cat_id',
                    'gxpress_required_attributes',
                    'gxpress_attributes'
                ],
                is_array($categories) ? $categories : array()

            );
        } catch (\Exception $e) {
            /*$file = fopen("/opt/xampp/7.2/htdocs/m2/m2.3/app/code/Ced/GXpress/log.txt","w");
            fwrite($file,$e->getMessage());
            fclose($file);*/
        }

        try {
            $table = $setup->getTable('gxpress_attribute');
            if($table) {
                $setup->getConnection()->truncateTable($table);
            }
            $setup->getConnection()->insertArray($table,
                [
                    "id",
                    "gxpress_attribute_name",
                    "magento_attribute_code",
                    "gxpress_attribute_doc",
                    "is_mapped",
                    "gxpress_attribute_enum",
                    "gxpress_attribute_level",
                    "gxpress_attribute_type",
                    "gxpress_attribute_depends_on",
                    "default_value"
                ],
                is_array($attributes) ? $attributes : array()
            );
        } catch (\Exception $e) {
            /*$file = fopen("/opt/xampp/7.2/htdocs/m2/m2.3/app/code/Ced/GXpress/log.txt","w");
            fwrite($file,$e->getMessage());
            fclose($file);*/
        }

        try {
            $table = $setup->getTable('gxpress_confattribute');
            if($table) {
                $setup->getConnection()->truncateTable($table);
            }
            $setup->getConnection()->insertArray($table,
                [
                    'id',
                    'gxpress_attribute_name',
                    'magento_attribute_code',
                    'gxpress_attribute_doc',
                    'is_mapped',
                    'gxpress_attribute_enum',
                    'gxpress_attribute_level',
                    'gxpress_attribute_type',
                    'gxpress_attribute_depends_on',
                ],
                is_array($confattributes) ? $confattributes : array()
            );
        } catch (\Exception $e) {
            /*$file = fopen("/opt/xampp/7.2/htdocs/m2/m2.3/app/code/Ced/GXpress/log.txt","w");
            fwrite($file,$e->getMessage());
            fclose($file);*/
        }

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */

        $groupName = 'gxpress';
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 1000);
        $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

        /*if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_feed_response')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_feed_response',
                [
                    'group' => 'GXpress',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'GXpress Feed Response',
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }*/

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_productid')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_productid',
                [
                    'group' => 'GXpress',
                    'note' => ' 1 to 14 characters, Alphanumeric ID that uniquely identifies the product. UPC|GTIN|MPN|ISBN',
                    'frontend_class' => 'validate-length maximum-length-14',
                    'input' => 'text',
                    'type' => 'varchar',
                    'label' => 'Google Express Product Id',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 3,
                    'user_defined' => 1,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }

        /*if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_productid_type')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_productid_type',
                [
                    'group' => 'GXpress',
                    'note' => 'Type of unique identifier used in the "Product ID" field.
                 Example: GTIN; MPN',
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'Google Express Product Id Type',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 2,
                    'user_defined' => 1,
                    'source' => 'Ced\GXpress\Model\Source\Pricetype',
                    'searchable' => 1,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'is_configurable' => false,
                ]
            );
        }*/
        /*if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_availability')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_availability',
                [
                    'group' => 'GXpress',
                    'note' => 'Googleexpress Product Availability',
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'Google Express Product Availability',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'source' => 'Ced\GXpress\Model\Source\Productavailability',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'is_configurable' => false,
                ]
            );
        }*/

        /*if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_language')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_language',
                [
                    'group' => 'GXpress',
                    'note' => 'Googleexpress Content language',
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'Google Express Content language',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'source' => 'Ced\GXpress\Model\Source\Contentlanguage',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'is_configurable' => false,
                ]
            );
        }*/

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_profile_id')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_profile_id',
                [
                    'group' => 'GXpress',
                    'note' => 'Googleexpress Profile Id ',
                    'input' => 'text',
                    'type' => 'varchar',
                    'label' => 'Google Express Profile Id',
                    'backend' => '',
                    'visible' => 0,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'is_configurable' => false,
                ]
            );
        }

        /*if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_country')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_country',
                [
                    'group' => 'GXpress',
                    'note' => 'Googleexpress Product Country',
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'Google Express Product Country',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'source' => 'Ced\GXpress\Model\Source\Productcountry',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'is_configurable' => false,
                ]
            );
        }*/

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_size')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_size',
                [
                    'group' => 'GXpress',
                    'note' => 'Googleexpress Product Size',
                    'input' => 'text',
                    'type' => 'varchar',
                    'label' => 'Google Express Product Size',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_color')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_color',
                [
                    'group' => 'GXpress',
                    'note' => 'Googleexpress Product Color',
                    'input' => 'text',
                    'type' => 'varchar',
                    'label' => 'Google Express Product Color',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_agegroup')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_agegroup',
                [
                    'group' => 'GXpress',
                    'note' => "select newborn (Up to 3 months old),\r\n infant (Between 3-12 months old),\r\n toddler (Between 1-5 years old),\r\n kids (Between 5-13 years old),\r\n adult(Typically teens or older)",
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'Google Express Product Age Group',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'is_configurable' => 1,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'source' => 'Ced\GXpress\Model\Source\Productagegroup',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_condition')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_condition',
                [
                    'group' => 'GXpress',
                    'note' => 'Googleexpress Product Condition',
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'Google Express Product Condition',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'source' => 'Ced\GXpress\Model\Source\Productcondition',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'is_configurable' => false
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_product_status')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_product_status',
                [
                    'group' => 'GXpress',
                    'note' => 'Googleexpress Product Status',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Google Express Product Status',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'source' => 'Ced\GXpress\Model\Source\Productstatus',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_product_url')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_product_url',
                [
                    'group' => 'GXpress',
                    'note' => 'Googleexpress Product Url',
                    'input' => 'hidden',
                    'type' => 'text',
                    'label' => 'Google Express Product Url',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_product_validation')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_product_validation',
                [
                    'group' => 'GXpress',
                    'note' => 'Googleexpress Product Validation',
                    'input' => 'hidden',
                    'type' => 'text',
                    'label' => 'Google Express Product Validation',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_brand')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_brand',
                [
                    'group' => 'GXpress',
                    'note' => '1 to 4000 characters',
                    'frontend_class' => 'validate-length maximum-length-4000',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Google Express Brand',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 4,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'gxpress_product_tax')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'gxpress_product_tax',
                [
                    'group' => 'GXpress',
                    'note' => '1 -  10 characters, Code used to identify tax properties of the product',
                    'frontend_class' => 'validate-length maximum-length-10',
                    'input' => 'text',
                    'type' => 'varchar',
                    'label' => 'Google Express Product Tax',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }
    }
}
