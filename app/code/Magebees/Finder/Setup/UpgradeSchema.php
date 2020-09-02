<?php
namespace Magebees\Finder\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
 
        //handle all possible upgrade versions
 
        if (!$context->getVersion()) {
            //no previous version found, installation, InstallSchema was just executed
            //be careful, since everything below is true for installation !
        }
        
        if (version_compare($context->getVersion(), '1.3.0') < 0) {
            //code to upgrade to 1.3.0
            
            $table = $installer->getConnection()
            ->newTable($installer->getTable('magebees_finder_universal_products'))
            ->addColumn(
                'universal_product_id',
                Table::TYPE_INTEGER,
                null,
                [
                'identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )
            ->addColumn('finder_id', Table::TYPE_INTEGER, 10, ['nullable' => false,'unsigned' => true])
            ->addColumn('sku', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('product_id', Table::TYPE_INTEGER, 10, ['nullable' => false])
            ->addIndex(
                $installer->getIdxName('IDX_FINDER_UNIVERSAL_PRODUCT_ID', ['finder_id']),
                ['finder_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'magebees_finder_universal_products',
                    'finder_id',
                    'magebees_finder',
                    'finder_id'
                ),
                'finder_id',
                $installer->getTable('magebees_finder'),
                'finder_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            )
            ->setComment('Finder To Universal Products Relations');

            $installer->getConnection()->createTable($table);
        }
        ///Updated to use object manager
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion(); //will return the magento version
        
        if (version_compare($version, '2.2.0')  >= 0) {
            $highervalue = 1;
            $lowervalue = 0;
        } else {
            $highervalue = 0;
            $lowervalue = 1;
        }
                //for check magento vserion
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'finder/general/lowerversion',
            'value' => $lowervalue,
        ];
        $installer->getConnection()
           ->insertOnDuplicate($installer->getTable('core_config_data'), $data, ['value']);
        
         $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'finder/general/higherversion',
            'value' => $highervalue,
         ];
         $installer->getConnection()
           ->insertOnDuplicate($installer->getTable('core_config_data'), $data, ['value']);
        
               $table = $installer->getConnection()
                ->newTable($installer->getTable('magebees_finder_ymm_value'))
                ->addColumn(
                    'ymm_value_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                    'identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true
                    ]
                )
               
                ->addColumn('dropdown_id', Table::TYPE_INTEGER, 10, ['nullable' => false,'unsigned' => true])
                ->addColumn('parent_id', Table::TYPE_INTEGER, 10, ['nullable' => false,'unsigned' => true])
                ->addColumn('value', Table::TYPE_TEXT, 255, ['nullable' => false])
                ->addIndex(
                    $installer->getIdxName('IDX_FINDER_YMM_VALUE_DROPDOWN_ID', ['dropdown_id']),
                    ['dropdown_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'magebees_finder_ymm_value',
                        'dropdown_id',
                        'magebees_finder_dropdowns',
                        'dropdown_id'
                    ),
                    'dropdown_id',
                    $installer->getTable('magebees_finder_dropdowns'),
                    'dropdown_id',
                    Table::ACTION_CASCADE,
                    Table::ACTION_CASCADE
                )
                ->setComment('Dropdown values Relations');

            $installer->getConnection()->createTable($table);
            
            $table = $installer->getConnection()
                ->newTable($installer->getTable('magebees_finder_map_value'))
                ->addColumn(
                    'map_value_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                    'identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true
                    ]
                )
                ->addColumn('ymm_value_id', Table::TYPE_INTEGER, 10, ['nullable' => false,'unsigned' => true])
                ->addColumn('product_id', Table::TYPE_TEXT, '2M', ['nullable' => false])
                ->addColumn('sku', Table::TYPE_TEXT, '2M', ['nullable' => false])
                ->addIndex(
                    $installer->getIdxName('IDX_FINDER_MAP_VALUE_YMM_VALUE_ID', ['ymm_value_id']),
                    ['ymm_value_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'magebees_finder_map_value',
                        'ymm_value_id',
                        'magebees_finder_ymm_value',
                        'ymm_value_id'
                    ),
                    'ymm_value_id',
                    $installer->getTable('magebees_finder_ymm_value'),
                    'ymm_value_id',
                    Table::ACTION_CASCADE,
                    Table::ACTION_CASCADE
                )
                ->setComment('Dropdown values to products relations');

            $installer->getConnection()->createTable($table);
        
        
        $installer->endSetup();
    }
}
