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
 * @package     Ced_GXpress
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Setup;

use Braintree\Exception;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
if (defined('DS') === false) define('DS', DIRECTORY_SEPARATOR);

class UpgradeData implements UpgradeDataInterface
{
    /**
     * directoryList
     * @var directoryList
     */
    public $directoryList;

    /**
     * EAV setup factory
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public  $objectManager;

    public  $logger;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\State $state
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\State $state
    )
    {
        $this->objectManager = $objectManager;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->directoryList = $directoryList;
        $this->logger = $logger;
        if(!$state->getAreaCode()) {
            $state->setAreaCode('frontend');
        }

    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
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

        if (version_compare($context->getVersion(), '0.0.2', '<')) {
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
            }
        }
    }
}