<?php


namespace Ced\TradeMe\Setup;


use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class InstallSchema
 * @package Ced\TradeMe\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('trademe_orders');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'trademe_orders'
             */
            $table = $installer->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'magento_order_id',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => false,
                    ),
                    'Magento Order Id'
                )
                ->addColumn(
                    'trademe_order_id',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => false,
                    ),
                    'TradeMe Order Id'
                )
                ->addColumn(
                    'failed_order_reason',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => '', 'unsigned' => true],
                    'Failed Order Reason'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => true,
                    ),
                    'Status'
                )
                ->addColumn(
                    'order_place_date',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Order Place Date'
                )
                ->addColumn(
                    'order_data',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Order Data'
                )
                ->addColumn(
                    'shipment_data',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Shipment Data'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Account Id'
                );
            $installer->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('trademe_product_change');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'trademe_product_change'
             */
            $table = $setup->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Profile Status'
                )
                ->addColumn(
                    'old_value',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Old Value'
                )
                ->addColumn(
                    'new_value',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'New Value'
                )
                ->addColumn(
                    'action',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Action'
                )
                ->addColumn(
                    'cron_type',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Cron type'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    50,
                    ['nullable' => true, 'default' => 0],
                    'Account Id'
                )
                ->addColumn(
                    'threshold_limit',
                    Table::TYPE_INTEGER,
                    50,
                    ['nullable' => true, 'default' => 0],
                    'Threshold Limit'
                )
                ->setComment('TradeMe Product Change')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

            $setup->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('trademe_categories');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'trademe_categories'
             */
            $table = $installer->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'code',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Code'
                )
                ->addColumn(
                    'label',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => false,
                    ),
                    'Label'
                )
                ->addColumn(
                    'trademe_id',
                    Table::TYPE_TEXT,
                    100,
                    array(
                        'nullable' => false,
                    ),
                    'Trademe Id'
                )
                ->addColumn(
                    'parent_code',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Parent Code'
                )
                ->addColumn(
                    'level',
                    Table::TYPE_SMALLINT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Level'
                )
                ->addColumn(
                    'attributes',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Trademe Attributes'
                )
                ->addColumn(
                    'is_leaf',
                    Table::TYPE_BOOLEAN,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Is Leaf'
                );
            $installer->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('trademe_profile');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'trademe_profile'
             */
            $table = $setup->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'profile_code',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => false,
                    ),
                    'Profile Code'
                )
                ->addColumn(
                    'profile_status',
                    Table::TYPE_BOOLEAN,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Product Status'
                )
                ->addColumn(
                    'profile_name',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => false,
                    ),
                    'Product Name'
                )
                ->addColumn(
                    'profile_category',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Product Category'
                )
                ->addColumn(
                    'opt_req_attribute',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Required-Optional Attribute Mapping'
                )
                ->addColumn(
                    'cat_depend_attribute',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Category Dependent Attribute Mapping'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    50,
                    array(
                        'nullable' => true,
                    ),
                    'Account Id'
                );

            $setup->getConnection()->createTable($table);
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('trademe_invetorycron'))) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('trademe_invetorycron'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'product_ids',
                    Table::TYPE_TEXT,
                    null, array(
                    'nullable' => false,
                ), 'Product Ids'
                )
                ->addColumn(
                    'strat_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Strat Time'
                )
                ->addColumn(
                    'finish_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Finish Time'
                )
                ->addColumn(
                    'cron_status',
                    Table::TYPE_TEXT,
                    25, array(
                    'nullable' => true,
                ), 'Cron Status'
                )
                ->addColumn(
                    'message',
                    Table::TYPE_TEXT,
                    500, array(
                    'nullable' => true,
                ), 'Error'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('trademe_product_change'))) {
            $setup->startSetup();
            $table = $setup->getConnection()
                ->newTable($setup->getTable('trademe_product_change'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ),
                    'ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    array('unsigned' => true, 'nullable' => false),
                    'Profile Status'
                )
                ->addColumn(
                    'old_value',
                    Table::TYPE_TEXT,
                    50,
                    array('nullable' => true, 'default' => ''),
                    'Old Value'
                )
                ->addColumn(
                    'new_value',
                    Table::TYPE_TEXT,
                    50,
                    array('nullable' => true, 'default' => ''),
                    'New Value'
                )
                ->addColumn(
                    'action',
                    Table::TYPE_TEXT,
                    50,
                    array('nullable' => true, 'default' => ''),
                    'Action'
                )
                ->addColumn(
                    'cron_type',
                    Table::TYPE_TEXT,
                    50,
                    array('nullable' => true, 'default' => ''),
                    'Cron type'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('trademe_accounts'))) {
            $setup->startSetup();
            $table = $setup->getConnection()->newTable($setup->getTable('trademe_accounts'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'account_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Account Code'
                )
                ->addColumn(
                    'account_env',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Account Environment'
                )
                ->addColumn(
                    'account_location',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Account Location'
                )
                ->addColumn(
                    'account_store',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Account Store'
                )
                ->addColumn(
                    'outh_verifier',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Outh Verifier'
                )
                ->addColumn(
                    'outh_consumer_key',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Outh Consumer Key'
                )
                ->addColumn(
                    'outh_consumer_secret',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Outh Consumer Secret'
                )
                ->addColumn(
                    'outh_token_secret',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Outh Token Secret'
                )
                ->addColumn(
                    'outh_access_token',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Outh Access Token'
                )
                ->addColumn(
                    'account_status',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => true,],
                    'Account Status'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }
        if (!$setup->getConnection()->isTableExists($setup->getTable('trademe_queue'))) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('trademe_queue'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ],
                    'Id'
                )
                ->addColumn(
                    'product_ids',
                    Table::TYPE_TEXT,
                    "2M",
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Product Ids'
                )
                ->addColumn(
                    'start_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Start Time'
                )
                ->addColumn(
                    'finish_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Finish Time'
                )
                ->addColumn(
                    'cron_status',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Cron Status'
                )
                ->addColumn(
                    'feed_file_path',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Path Of Feed File'
                )
                ->addColumn(
                    'error',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Errors'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'Account Id'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('trademe_logs'))) {
            $setup->startSetup();
            $table = $setup->getConnection()->newTable($setup->getTable('trademe_logs'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true
                    ),
                    'Id'
                )
                ->addColumn(
                    'log_type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Log Type'
                )
                ->addColumn(
                    'log_sub_type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Log Sub Types'
                )
                ->addColumn(
                    'log_date',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Log Date'
                )
                ->addColumn(
                    'log_comment',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Log Comment'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }
    }
}
