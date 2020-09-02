<?php

namespace Ced\Amazon\Setup;

use Ced\Amazon\Api\Data\Search\ProductInterface;
use Ced\Amazon\Api\Data\Strategy\AssignmentInterface;
use Ced\Amazon\Api\Data\Strategy\AttributeInterface;
use Ced\Amazon\Api\Data\StrategyInterface;
use Ced\Amazon\Model\Account;
use Ced\Amazon\Model\Feed;
use Ced\Amazon\Model\Order;
use Ced\Amazon\Model\Order\Item;
use Ced\Amazon\Model\Product;
use Ced\Amazon\Model\Profile;
use Ced\Amazon\Model\Queue;
use Ced\Amazon\Model\Report;
use Ced\Amazon\Model\Strategy;
use Ced\Amazon\Model\Strategy\Assignment;
use Ced\Amazon\Model\Strategy\Attribute;
use Ced\Amazon\Model\Strategy\GlobalStrategy;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Tax\Model\Calculation\RateFactory;
use Magento\Tax\Model\Calculation\RuleFactory;
use Magento\Tax\Model\ResourceModel\Calculation\Rate;
use Magento\Tax\Model\ResourceModel\Calculation\Rule;
use Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory;

/**
 * Class UpgradeSchema
 * @package Ced\Amazon\Setup
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public $rateFactory;
    public $rate;
    public $scopeConfig;
    public $ruleFactory;
    public $rule;
    public $taxClassModel;

    public function __construct(
        RateFactory $rateFactory,
        Rate $rate,
        ScopeConfigInterface $scopeConfig,
        RuleFactory $ruleFactory,
        Rule $rule,
        CollectionFactory $taxClassModel
    ) {
        $this->rateFactory = $rateFactory;
        $this->rate = $rate;
        $this->scopeConfig = $scopeConfig;
        $this->ruleFactory = $ruleFactory;
        $this->rule = $rule;
        $this->taxClassModel = $taxClassModel;
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            /**
             * Creating `ced_amazon_account` table
             */
            if (!$installer->getConnection()->isTableExists($installer->getTable(Account::NAME))) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable(Account::NAME))
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
                        'Id'
                    )
                    ->addColumn(
                        'name',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => false,
                            'default' => ''
                        ],
                        'Name'
                    )
                    ->addColumn(
                        'mode',
                        Table::TYPE_TEXT,
                        25,
                        [
                            'nullable' => true,
                        ],
                        'Mode'
                    )
                    ->addColumn(
                        'seller_id',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'Seller Id'
                    )
                    ->addIndex(
                        $installer->getIdxName($installer->getTable(Account::NAME), ['seller_id']),
                        ['seller_id']
                    )
                    ->addColumn(
                        'marketplace',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'Marketplace'
                    )
                    ->addColumn(
                        'aws_access_key_id',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'AWS Acess Key Id'
                    )
                    ->addColumn(
                        'aws_auth_id',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'AWS Auth Id'
                    )
                    ->addColumn(
                        'secret_key',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'Secret Key'
                    )
                    ->addColumn(
                        'active',
                        Table::TYPE_BOOLEAN,
                        null,
                        [
                            'nullable' => true,
                            'default' => 0
                        ],
                        'Active'
                    )
                    ->addColumn(
                        'status',
                        Table::TYPE_TEXT,
                        50,
                        [
                            'nullable' => true,
                            'default' => 'ADDED'
                        ],
                        'Status'
                    )
                    ->addColumn(
                        'notes',
                        Table::TYPE_TEXT,
                        2000,
                        [
                            'nullable' => true,
                            'default' => ''
                        ],
                        'Notes'
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            $installer->getTable(Account::NAME),
                            ['name', 'status'],
                            AdapterInterface::INDEX_TYPE_FULLTEXT
                        ),
                        [
                            'name',   // filed or column name
                            'status',   // filed or column name
                        ],
                        ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
                    )
                    ->addColumn(
                        'store_id',
                        Table::TYPE_SMALLINT,
                        null,
                        ['unsigned' => true, 'default' => '0'],
                        'Store Id'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Account::NAME),
                            'store_id',
                            'store',
                            'store_id'
                        ),
                        'store_id',
                        $installer->getTable('store'),
                        'store_id',
                        Table::ACTION_CASCADE
                    )
                    ->setComment('Amazon Account');
                $installer->getConnection()->createTable($table);
            }

            /**
             * Creating `ced_amazon_report` table
             */
            if (!$installer->getConnection()->isTableExists($installer->getTable(Report::NAME))) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable(Report::NAME))
                    ->addColumn(
                        Report::COLUMN_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Id'
                    )
                    ->addColumn(
                        Report::COLUMN_REQUEST_ID,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => false,
                        ],
                        'Request Id'
                    )
                    ->addColumn(
                        Report::COLUMN_REPORT_ID,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'Report Id'
                    )
                    ->addColumn(
                        Report::COLUMN_ACCOUNT_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false,
                        ],
                        'Account Id'
                    )
                    ->addColumn(
                        Report::COLUMN_TYPE,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'Type'
                    )
                    ->addColumn(
                        Report::COLUMN_STATUS,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'Status'
                    )
                    ->addColumn(
                        Report::COLUMN_REPORT_FILE,
                        Table::TYPE_TEXT,
                        1000,
                        [
                            'nullable' => true
                        ],
                        'Report File'
                    )
                    ->addColumn(
                        Report::COLUMN_SPECIFICS,
                        Table::TYPE_TEXT,
                        '2M',
                        [
                            'nullable' => true
                        ],
                        'Specifics'
                    )
                    ->addColumn(
                        Report::COLUMN_CREATED_AT,
                        Table::TYPE_TIMESTAMP,
                        null,
                        [
                            'nullable' => false,
                            'default' => Table::TIMESTAMP_INIT
                        ],
                        'Created At'
                    )
                    ->addColumn(
                        Report::COLUMN_EXECUTED_AT,
                        Table::TYPE_DATETIME,
                        null,
                        [
                            'nullable' => true
                        ],
                        'Executed At'
                    )
                    ->setComment('Amazon Report');
                $installer->getConnection()->createTable($table);
            }

            /**
             * Updating `ced_amazon_order` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(Order::NAME))) {
                /**
                 * Adding column 'marketplace_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_MARKETPLACE_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_MARKETPLACE_ID,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'Marketplace Id',
                            'after' => Order::COLUMN_PO_ID
                        ]
                    );
                }
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_INVOICE_UPLOAD_STATUS
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_INVOICE_UPLOAD_STATUS,
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'comment' => 'Invoice Upload Status',
                            'default' => '0',
                        ]
                    );
                }

                /**

                /**
                 * Adding column 'account_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_ACCOUNT_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_ACCOUNT_ID,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'comment' => 'Account Id',
                            'after' => Order::COLUMN_MARKETPLACE_ID
                        ]
                    );

                    /**
                     * Adding foreign key
                     */
                    $installer->getConnection()->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Order::NAME),
                            Order::COLUMN_ACCOUNT_ID,
                            $installer->getTable(Account::NAME),
                            Account::ID_FIELD_NAME
                        ),
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_ACCOUNT_ID,
                        $installer->getTable(Account::NAME),
                        Account::ID_FIELD_NAME,
                        Table::ACTION_CASCADE
                    );
                }

                /**
                 * Adding column 'reason'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_FAILURE_REASON
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_FAILURE_REASON,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'comment' => 'Failure Reason',
                            'after' => Order::COLUMN_ADJUSTMENT_DATA
                        ]
                    );
                }
            }

            /**
             * Updating `ced_amazon_profile` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(Profile::NAME))) {
                /**
                 * Adding column 'marketplace'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_MARKETPLACE
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_MARKETPLACE,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => true,
                            'length' => 1000,
                            'unsigned' => true,
                            'comment' => 'Marketplace',
                            'after' => Profile::COLUMN_OPTIONAL_ATTRIBUTES
                        ]
                    );
                }

                /**
                 * Adding column 'query'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_QUERY
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_QUERY,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => true,
                            'unsigned' => true,
                            'comment' => 'Query',
                            'after' => Profile::COLUMN_MARKETPLACE
                        ]
                    );
                }

                /**
                 * Adding column 'account_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_ACCOUNT_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_ACCOUNT_ID,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'comment' => 'Account Id',
                            'after' => Profile::COLUMN_QUERY
                        ]
                    );

                    /**
                     * Adding foreign key
                     */
                    $installer->getConnection()->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Profile::NAME),
                            Profile::COLUMN_ACCOUNT_ID,
                            $installer->getTable(Account::NAME),
                            Account::ID_FIELD_NAME
                        ),
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_ACCOUNT_ID,
                        $installer->getTable(Account::NAME),
                        Account::ID_FIELD_NAME,
                        Table::ACTION_CASCADE
                    );
                }

                /**
                 * Adding column 'store_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_STORE_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STORE_ID,
                        [
                            'type' => Table::TYPE_SMALLINT,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'default' => 0,
                            'comment' => 'Store Id',
                            'after' => Profile::COLUMN_ACCOUNT_ID
                        ]
                    );
                    /**
                     * Adding foreign key
                     */
                    $installer->getConnection()->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Profile::NAME),
                            Profile::COLUMN_STORE_ID,
                            $installer->getTable('store'),
                            'store_id'
                        ),
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STORE_ID,
                        $installer->getTable('store'),
                        'store_id',
                        Table::ACTION_CASCADE
                    );
                }

                /**
                 * Removing column 'profile_code'
                 */
                if ($installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    'profile_code'
                )) {
                    $installer->getConnection()->dropColumn(
                        $installer->getTable(Profile::NAME),
                        'profile_code'
                    );
                    $installer->getConnection()->dropIndex(
                        $installer->getTable(Profile::NAME),
                        $installer->getIdxName(
                            $installer->getTable(Profile::NAME),
                            ['profile_code'],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        )
                    );
                }
            }

            /*
             * Updating `ced_amazon_feed` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(Feed::NAME))) {
                /**
                 * Adding column 'specifics'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Feed::NAME),
                    Feed::COLUMN_SPECIFICS
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Feed::NAME),
                        Feed::COLUMN_SPECIFICS,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => true,
                            'length' => "2M",
                            'comment' => 'Specifics',
                            'after' => Feed::COLUMN_RESPONSE_FILE
                        ]
                    );
                }
            }

            /*
             * Updating `ced_amazon_queue` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(Queue::NAME))) {
                /**
                 * Adding column 'specifics'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Queue::NAME),
                    Queue::COLUMN_SPECIFICS
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Queue::NAME),
                        Queue::COLUMN_SPECIFICS,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => true,
                            'length' => "2M",
                            'comment' => 'Specifics',
                            'after' => Queue::COLUMN_DEPENDS
                        ]
                    );
                }

                /**
                 * Adding column 'account_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Queue::NAME),
                    Queue::COLUMN_ACCOUNT_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Queue::NAME),
                        Queue::COLUMN_ACCOUNT_ID,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'comment' => 'Account Id',
                            'after' => Queue::COLUMN_ID
                        ]
                    );
                }

                /**
                 * Adding foreign key
                 */
                $installer->getConnection()->addForeignKey(
                    $installer->getFkName(
                        $installer->getTable(Queue::NAME),
                        Queue::COLUMN_ACCOUNT_ID,
                        $installer->getTable(Account::NAME),
                        Account::ID_FIELD_NAME
                    ),
                    $installer->getTable(Queue::NAME),
                    Queue::COLUMN_ACCOUNT_ID,
                    $installer->getTable(Account::NAME),
                    Account::ID_FIELD_NAME,
                    Table::ACTION_CASCADE
                );

                /**
                 * Adding column 'marketplace'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Queue::NAME),
                    Queue::COLUMN_MARKETPLACE
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Queue::NAME),
                        Queue::COLUMN_MARKETPLACE,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'Marketplace',
                            'after' => Queue::COLUMN_ACCOUNT_ID
                        ]
                    );
                }

                /**
                 * Altering column 'created_at'
                 */
                if ($installer->getConnection()->tableColumnExists(
                    $installer->getTable(Queue::NAME),
                    Queue::COLUMN_CREATED_AT
                )) {
                    $installer->getConnection()->changeColumn(
                        $installer->getTable(Queue::NAME),
                        Queue::COLUMN_CREATED_AT,
                        Queue::COLUMN_CREATED_AT,
                        [
                            'type' => Table::TYPE_TIMESTAMP,
                            'default' => Table::TIMESTAMP_INIT,
                            'nullable' => false,
                            'length' => null,
                            'comment' => 'Created At',
                            'after' => Queue::COLUMN_SPECIFICS
                        ]
                    );
                }

                /**
                 * Altering column 'executed_at'
                 */
                if ($installer->getConnection()->tableColumnExists(
                    $installer->getTable(Queue::NAME),
                    Queue::COLUMN_EXECUTED_AT
                )) {
                    $installer->getConnection()->changeColumn(
                        $installer->getTable(Queue::NAME),
                        Queue::COLUMN_EXECUTED_AT,
                        Queue::COLUMN_EXECUTED_AT,
                        [
                            'type' => Table::TYPE_DATETIME,
                            'default' => null,
                            'nullable' => true,
                            'length' => null,
                            'comment' => 'Executed At',
                            'after' => Queue::COLUMN_CREATED_AT
                        ]
                    );
                }
            }

            /**
             * Updating `ced_amazon_feed` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(Feed::NAME))) {
                /**
                 * Adding column 'account_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Feed::NAME),
                    Feed::COLUMN_ACCOUNT_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Feed::NAME),
                        Feed::COLUMN_ACCOUNT_ID,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'comment' => 'Account Id',
                            'after' => Feed::COLUMN_ID
                        ]
                    );
                }

                /**
                 * Adding foreign key
                 */
                $installer->getConnection()->addForeignKey(
                    $installer->getFkName(
                        $installer->getTable(Feed::NAME),
                        Feed::COLUMN_ACCOUNT_ID,
                        $installer->getTable(Account::NAME),
                        Account::ID_FIELD_NAME
                    ),
                    $installer->getTable(Feed::NAME),
                    Feed::COLUMN_ACCOUNT_ID,
                    $installer->getTable(Account::NAME),
                    Account::ID_FIELD_NAME,
                    Table::ACTION_CASCADE
                );
            }
        }

        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            /**
             * Updating `ced_amazon_account` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(Account::NAME))) {
                /**
                 * Adding column 'channel'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Account::NAME),
                    Account::COLUMN_CHANNEL
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Account::NAME),
                        Account::COLUMN_CHANNEL,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 50,
                            'unsigned' => true,
                            'comment' => 'Channel',
                            'after' => Account::COLUMN_STORE_ID
                        ]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            /**
             * Updating `ced_amazon_account` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(Account::NAME))) {
                /**
                 * Adding column 'shipping_method'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Account::NAME),
                    Account::COLUMN_SHIPPING_METHOD
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Account::NAME),
                        Account::COLUMN_SHIPPING_METHOD,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => '2M',
                            'unsigned' => true,
                            'comment' => 'Shipping Method',
                            'after' => Account::COLUMN_STORE_ID
                        ]
                    );
                }

                /**
                 * Adding column 'payment_method'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Account::NAME),
                    Account::COLUMN_PAYMENT_METHOD
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Account::NAME),
                        Account::COLUMN_PAYMENT_METHOD,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'Payment Method',
                            'default' => 'paybyamazon',
                            'after' => Account::COLUMN_STORE_ID
                        ]
                    );
                }

                /** adding order prefix column */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Account::NAME),
                    Account::COLUMN_ORDER_PREFIX
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Account::NAME),
                        Account::COLUMN_ORDER_PREFIX,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'order_prefix',
                            'after' => Account::COLUMN_PAYMENT_METHOD
                        ]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '0.0.9', '<')) {
            /**
             * Updating `ced_amazon_profile` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(Profile::NAME))) {
                /**
                 * Adding column 'barcode_exemption'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_BARCODE_EXEMPTION
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_BARCODE_EXEMPTION,
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'nullable' => false,
                            'comment' => 'Barcode Exemption',
                            'default' => '0',
                            'after' => Profile::COLUMN_STORE_ID
                        ]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '0.1.0', '<')) {
            // adding column cedcommerce
            $tableName = $installer->getTable(Account::NAME);
            $connection = $installer->getConnection();

            if (!$connection->tableColumnExists($tableName, Account::COLUMN_CEDCOMMERCE)) {
                $connection->addColumn(
                    $tableName,
                    Account::COLUMN_CEDCOMMERCE,
                    [
                        'type' => Table::TYPE_BOOLEAN,
                        'nullable' => true,
                        'comment' => 'Authorize via Cedcommerce',
                        'default' => 0,
                        'after' => Account::COLUMN_STATUS
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            /**
             * Updating `ced_amazon_account` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(Account::NAME))) {
                /**
                 * Adding column 'multi_store'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Account::NAME),
                    Account::COLUMN_MULTI_STORE
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Account::NAME),
                        Account::COLUMN_MULTI_STORE,
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'nullable' => true,
                            'comment' => 'Multi Store',
                            'default' => 0,
                            'after' => Account::COLUMN_STORE_ID
                        ]
                    );
                }

                /**
                 * Adding column 'multi_store_values'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Account::NAME),
                    Account::COLUMN_MULTI_STORE_VALUES
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Account::NAME),
                        Account::COLUMN_MULTI_STORE_VALUES,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 500,
                            'unsigned' => true,
                            'comment' => 'Multi Store Values',
                            'after' => Account::COLUMN_STORE_ID
                        ]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '0.1.8', '<')) {
            if ($installer->getConnection()->isTableExists($installer->getTable(Order::NAME))) {
                /**
                 * Updating `ced_amazon_order` table
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_FULFILLMENT_CHANNEL
                )) {
                    /**
                     * Adding columns 'fulfillment_channel'
                     */
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_FULFILLMENT_CHANNEL,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'Fulfillment Channel',
                        ]
                    );
                }

                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_SALES_CHANNEL
                )) {
                    /**
                     * Adding columns 'sales_channel'
                     */
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_SALES_CHANNEL,
                        [
                            'type' => Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'Sales Channel',
                        ]
                    );
                }

                if ($installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    'order_place_date'
                )) {
                    /**
                     * Changing column 'order_place_date' to 'purchase_date'
                     */
                    $installer->getConnection()->changeColumn(
                        $installer->getTable(Order::NAME),
                        'order_place_date',
                        Order::COLUMN_PO_DATE,
                        [
                            'type' => Table::TYPE_DATETIME,
                            'nullable' => true,
                            'comment' => 'Purchase Date',
                        ]
                    );
                }

                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_LAST_UPDATE_DATE
                )) {
                    /**
                     * Adding columns 'fulfillment_channel'
                     */
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_LAST_UPDATE_DATE,
                        [
                            'type' => Table::TYPE_DATETIME,
                            'nullable' => true,
                            'comment' => 'Last Update Date In Amazon',
                        ]
                    );
                }

                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_CREATED_AT
                )) {
                    /**
                     * Adding columns 'created at'
                     */
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_CREATED_AT,
                        [
                            'type' => Table::TYPE_TIMESTAMP,
                            'nullable' => false,
                            'size' => null,
                            'default' => Table::TIMESTAMP_INIT,
                            'comment' => 'Created At',
                        ]
                    );
                }

                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_UPDATED_AT
                )) {
                    /**
                     * Adding columns 'updated at'
                     */
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_UPDATED_AT,
                        [
                            'type' => Table::TYPE_TIMESTAMP,
                            'nullable' => false,
                            'size' => null,
                            'default' => Table::TIMESTAMP_INIT_UPDATE,
                            'comment' => 'Updated At',
                        ]
                    );
                }

                /**
                 * Creating `ced_amazon_order_items` table
                 */
                if (!$installer->getConnection()->isTableExists($installer->getTable(
                    Item::NAME
                ))) {
                    try {
                        $table = $installer->getConnection()
                            ->newTable($installer->getTable(Item::NAME))
                            ->addColumn(
                                Item::COLUMN_ID,
                                Table::TYPE_INTEGER,
                                null,
                                [
                                    'identity' => true,
                                    'unsigned' => true,
                                    'nullable' => false,
                                    'primary' => true
                                ],
                                'Id'
                            )
                            ->addColumn(
                                Item::COLUMN_ASIN,
                                Table::TYPE_TEXT,
                                255,
                                [
                                    'nullable' => false,
                                ],
                                'ASIN'
                            )
                            ->addColumn(
                                Item::COLUMN_SKU,
                                Table::TYPE_TEXT,
                                255,
                                [
                                    'nullable' => false,
                                ],
                                'Seller SKU'
                            )
                            ->addColumn(
                                Item::COLUMN_ORDER_ITEM_ID,
                                Table::TYPE_TEXT,
                                null,
                                [
                                    'nullable' => false,
                                ],
                                'Order Item Id'
                            )
                            ->addColumn(
                                Item::COLUMN_ORDER_ID,
                                Table::TYPE_TEXT,
                                100,
                                [
                                    'nullable' => true
                                ],
                                'Amazon order Id'
                            )
                            ->addColumn(
                                Item::COLUMN_MAGENTO_ORDER_ITEM_ID,
                                Table::TYPE_INTEGER,
                                10,
                                [
                                    'nullable' => true,
                                    'unsigned' => true,
                                ],
                                'Magento Order Item Id'
                            )
                            ->addColumn(
                                Item::COLUMN_CUSTOMIZED_URL,
                                Table::TYPE_TEXT,
                                1000,
                                [
                                    'nullable' => true
                                ],
                                'Customized Zip Url'
                            )
                            ->addColumn(
                                Item::COLUMN_CUSTOMIZED_DATA,
                                Table::TYPE_TEXT,
                                '1000',
                                [
                                    'nullable' => true
                                ],
                                'Json Data From Zip'
                            )
                            ->addColumn(
                                Item::COLUMN_QTY_ORDERED,
                                Table::TYPE_INTEGER,
                                '10',
                                [
                                    'nullable' => false
                                ],
                                'Qunatity Ordered'
                            )
                            ->addColumn(
                                Item::COLUMN_QTY_SHIPPED,
                                Table::TYPE_INTEGER,
                                '10',
                                [
                                    'nullable' => true
                                ],
                                'Quantity shipped'
                            )
                            ->addColumn(
                                Item::COLUMN_CREATED_AT,
                                Table::TYPE_TIMESTAMP,
                                null,
                                [
                                    'nullable' => false,
                                    'default' => Table::TIMESTAMP_INIT
                                ],
                                'Created At'
                            )
                            ->addColumn(
                                Item::COLUMN_UPDATED_AT,
                                Table::TYPE_TIMESTAMP,
                                null,
                                [
                                    'nullable' => false,
                                    'default' => Table::TIMESTAMP_INIT_UPDATE
                                ],
                                'Updated At'
                            )
                            ->addForeignKey(
                                $installer->getFkName(
                                    $installer->getTable(Item::NAME),
                                    Item::COLUMN_ORDER_ID,
                                    $installer->getTable(Order::NAME),
                                    Order::COLUMN_PO_ID
                                ),
                                Item::COLUMN_ORDER_ID,
                                $installer->getTable(Order::NAME),
                                Order::COLUMN_PO_ID,
                                Table::ACTION_CASCADE
                            )
                            ->addForeignKey(
                                $installer->getFkName(
                                    $installer->getTable(Item::NAME),
                                    Item::COLUMN_MAGENTO_ORDER_ITEM_ID,
                                    $installer->getTable('sales_order_item'),
                                    'item_id'
                                ),
                                Item::COLUMN_MAGENTO_ORDER_ITEM_ID,
                                $installer->getTable('sales_order_item'),
                                'item_id',
                                Table::ACTION_SET_NULL
                            )
                            ->setComment('Amazon Order Items');
                        $installer->getConnection()->createTable($table);
                    } catch (\Zend_Db_Exception $exception) {
                        throwException($exception);
                    }
                }
            }
        }

        if (version_compare($context->getVersion(), '0.2.0', '<')) {
            if (!$installer->getConnection()->isTableExists(
                $installer->getTable(Profile\Product::NAME)
            )) {
                /**
                 * Create table 'ced_amazon_profile_product'
                 */
                $tableName = $installer->getTable(Profile\Product::NAME);
                $table = $installer->getConnection()
                    ->newTable($tableName)
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
                        Profile\Product::COLUMN_PRODUCT_ID,
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Product Id'
                    )
                    ->addColumn(
                        Profile\Product::COLUMN_PROFILE_ID,
                        Table::TYPE_INTEGER,
                        10,
                        ['unsigned' => true, 'nullable' => false],
                        'Profile Id'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Profile\Product::NAME),
                            Profile\Product::COLUMN_PRODUCT_ID,
                            $installer->getTable('catalog_product_entity'),
                            'entity_id'
                        ),
                        Profile\Product::COLUMN_PRODUCT_ID,
                        $installer->getTable('catalog_product_entity'),
                        'entity_id',
                        Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Profile\Product::NAME),
                            Profile\Product::COLUMN_PROFILE_ID,
                            $installer->getTable(Profile::NAME),
                            'id'
                        ),
                        Profile\Product::COLUMN_PROFILE_ID,
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_ID,
                        Table::ACTION_CASCADE
                    )->addIndex(
                        $installer->getIdxName(
                            $tableName,
                            [
                                Profile\Product::COLUMN_PROFILE_ID,
                                Profile\Product::COLUMN_PRODUCT_ID
                            ],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        [
                            Profile\Product::COLUMN_PROFILE_ID,
                            Profile\Product::COLUMN_PRODUCT_ID
                        ],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )->addIndex(
                        $installer->getIdxName(
                            $tableName,
                            [
                                Profile\Product::COLUMN_PROFILE_ID,
                                Profile\Product::COLUMN_PRODUCT_ID
                            ],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        [
                            Profile\Product::COLUMN_PROFILE_ID,
                            Profile\Product::COLUMN_PRODUCT_ID
                        ],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Amazon Profile Product');
                $installer->getConnection()->createTable($table);
            }

            if (!$installer->getConnection()->isTableExists(
                $installer->getTable(Strategy::NAME)
            )) {
                /**
                 * Create table 'ced_amazon_strategy'
                 */
                $tableName = $installer->getTable(Strategy::NAME);
                $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        StrategyInterface::COLUMN_ID,
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
                        StrategyInterface::COLUMN_ACTIVE,
                        Table::TYPE_BOOLEAN,
                        null,
                        [
                            'nullable' => true,
                            'default' => 0
                        ],
                        'Active'
                    )
                    ->addColumn(
                        StrategyInterface::COLUMN_TYPE,
                        Table::TYPE_TEXT,
                        50,
                        [
                            'nullable' => false
                        ],
                        'Type'
                    )
                    ->addColumn(
                        StrategyInterface::COLUMN_NAME,
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => false],
                        'Name'
                    )
                    ->setComment('Amazon Strategy');
                $installer->getConnection()->createTable($table);
            }

            if (!$installer->getConnection()->isTableExists(
                $installer->getTable(GlobalStrategy::NAME)
            )) {
                /**
                 * Create table 'ced_amazon_strategy_global'
                 */
                $tableName = $installer->getTable(GlobalStrategy::NAME);
                $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\GlobalStrategyInterface::COLUMN_ID,
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
                        \Ced\Amazon\Api\Data\Strategy\GlobalStrategyInterface::COLUMN_GLOBAL_RELATION_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false
                        ],
                        'Strategy Id'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_FULFILLMENT_LATENCY,
                        Table::TYPE_TEXT,
                        50,
                        [
                            'nullable' => true,
                        ],
                        'Fulfillment Latency'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_INVENTORY_OVERRIDE,
                        Table::TYPE_BOOLEAN,
                        null,
                        [
                            'nullable' => false,
                            'default' => 0,
                        ],
                        'Inventory Override'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD,
                        Table::TYPE_BOOLEAN,
                        null,
                        [
                            'nullable' => false,
                            'default' => 0,
                        ],
                        'Threshold'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD_BREAKPOINT,
                        Table::TYPE_TEXT,
                        50,
                        [
                            'nullable' => true,
                        ],
                        'Threshold Breakpoint'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD_GREATER_THAN_VALUE,
                        Table::TYPE_TEXT,
                        50,
                        [
                            'nullable' => true,
                        ],
                        'Threshold Greater Value'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD_LESS_THAN_VALUE,
                        Table::TYPE_TEXT,
                        50,
                        [
                            'nullable' => true,
                        ],
                        'Threshold Less Value'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\ShippingInterface::COLUMN_MERCHANT_SHIPPING_GROUP_NAME,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'Merchant Shipping Group Name'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(GlobalStrategy::NAME),
                            \Ced\Amazon\Api\Data\Strategy\GlobalStrategyInterface::COLUMN_GLOBAL_RELATION_ID,
                            $installer->getTable(Strategy::NAME),
                            StrategyInterface::COLUMN_ID
                        ),
                        \Ced\Amazon\Api\Data\Strategy\GlobalStrategyInterface::COLUMN_GLOBAL_RELATION_ID,
                        $installer->getTable(Strategy::NAME),
                        StrategyInterface::COLUMN_ID,
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            $tableName,
                            [
                                \Ced\Amazon\Api\Data\Strategy\GlobalStrategyInterface::COLUMN_GLOBAL_RELATION_ID,
                            ],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        [
                            \Ced\Amazon\Api\Data\Strategy\GlobalStrategyInterface::COLUMN_GLOBAL_RELATION_ID,
                        ],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Amazon Strategy Global');
                $installer->getConnection()->createTable($table);
            }

            if (!$installer->getConnection()->isTableExists(
                $installer->getTable(\Ced\Amazon\Model\Strategy\Inventory::NAME)
            )) {
                /**
                 * Create table 'ced_amazon_strategy_inventory'
                 */
                $tableName = $installer->getTable(\Ced\Amazon\Model\Strategy\Inventory::NAME);
                $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_INVENTORY_STRATEGY_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Id'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_INVENTORY_RELATION_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false
                        ],
                        'Strategy Id'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_FULFILLMENT_LATENCY,
                        Table::TYPE_TEXT,
                        50,
                        [
                            'nullable' => true,
                        ],
                        'Fulfillment Latency'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_INVENTORY_OVERRIDE,
                        Table::TYPE_BOOLEAN,
                        null,
                        [
                            'nullable' => false,
                            'default' => 0,
                        ],
                        'Inventory Override'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD,
                        Table::TYPE_BOOLEAN,
                        null,
                        [
                            'nullable' => false,
                            'default' => 0,
                        ],
                        'Threshold'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD_BREAKPOINT,
                        Table::TYPE_TEXT,
                        50,
                        [
                            'nullable' => true,
                        ],
                        'Threshold Breakpoint'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD_GREATER_THAN_VALUE,
                        Table::TYPE_TEXT,
                        50,
                        [
                            'nullable' => true,
                        ],
                        'Threshold Greater Value'
                    )
                    ->addColumn(
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_THRESHOLD_LESS_THAN_VALUE,
                        Table::TYPE_TEXT,
                        50,
                        [
                            'nullable' => true,
                        ],
                        'Threshold Less Value'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(\Ced\Amazon\Model\Strategy\Inventory::NAME),
                            \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_INVENTORY_RELATION_ID,
                            $installer->getTable(Strategy::NAME),
                            StrategyInterface::COLUMN_ID
                        ),
                        \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_INVENTORY_RELATION_ID,
                        $installer->getTable(Strategy::NAME),
                        StrategyInterface::COLUMN_ID,
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            $tableName,
                            [
                                \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_INVENTORY_RELATION_ID,
                            ],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        [
                            \Ced\Amazon\Api\Data\Strategy\InventoryInterface::COLUMN_INVENTORY_RELATION_ID,
                        ],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Amazon Strategy Inventory');
                $installer->getConnection()->createTable($table);
            }

            if (!$installer->getConnection()->isTableExists(
                $installer->getTable(Assignment::NAME)
            )) {
                /**
                 * Create table 'ced_amazon_strategy_assignment'
                 */
                $tableName = $installer->getTable(Assignment::NAME);
                $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        AssignmentInterface::COLUMN_ASSIGNMENT_STRATEGY_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Id'
                    )
                    ->addColumn(
                        AssignmentInterface::COLUMN_ASSIGNMENT_RELATION_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false
                        ],
                        'Strategy Id'
                    )
                    ->addColumn(
                        AssignmentInterface::COLUMN_CATEGORY_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => true,
                        ],
                        'Category Id'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Assignment::NAME),
                            AssignmentInterface::COLUMN_ASSIGNMENT_RELATION_ID,
                            $installer->getTable(Strategy::NAME),
                            StrategyInterface::COLUMN_ID
                        ),
                        AssignmentInterface::COLUMN_ASSIGNMENT_RELATION_ID,
                        $installer->getTable(Strategy::NAME),
                        StrategyInterface::COLUMN_ID,
                        Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Assignment::NAME),
                            AssignmentInterface::COLUMN_CATEGORY_ID,
                            $installer->getTable("catalog_category_entity"),
                            "entity_id"
                        ),
                        AssignmentInterface::COLUMN_CATEGORY_ID,
                        $installer->getTable("catalog_category_entity"),
                        "entity_id",
                        Table::ACTION_SET_NULL
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            $tableName,
                            [
                                AssignmentInterface::COLUMN_ASSIGNMENT_RELATION_ID,
                            ],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        [
                            AssignmentInterface::COLUMN_ASSIGNMENT_RELATION_ID,
                        ],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Amazon Strategy Assignment');
                $installer->getConnection()->createTable($table);
            }

            if (!$installer->getConnection()->isTableExists(
                $installer->getTable(Attribute::NAME)
            )) {
                /**
                 * Create table 'ced_amazon_strategy_attribute'
                 */
                $tableName = $installer->getTable(Attribute::NAME);
                $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        AttributeInterface::COLUMN_STRATEGY_ATTRIBUTE_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Id'
                    )
                    ->addColumn(
                        AttributeInterface::COLUMN_ATTRIBUTE_RELATION_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false
                        ],
                        'Strategy Id'
                    )
                    ->addColumn(
                        AttributeInterface::COLUMN_PRODUCT_TYPE,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'Product Type'
                    )
                    ->addColumn(
                        AttributeInterface::COLUMN_PRODUCT_SUB_TYPE,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'Product Sub Type'
                    )
                    ->addColumn(
                        AttributeInterface::COLUMN_BROWSE_NODES,
                        Table::TYPE_TEXT,
                        1000,
                        [
                            'nullable' => true,
                        ],
                        'Browse Node Ids'
                    )
                    ->addColumn(
                        AttributeInterface::COLUMN_SKU,
                        Table::TYPE_TEXT,
                        60,
                        [
                            'nullable' => false,
                        ],
                        'SKU'
                    )
                    ->addColumn(
                        AttributeInterface::COLUMN_ASIN,
                        Table::TYPE_TEXT,
                        60,
                        [
                            'nullable' => true,
                        ],
                        'ASIN'
                    )
                    ->addColumn(
                        AttributeInterface::COLUMN_UPC,
                        Table::TYPE_TEXT,
                        60,
                        [
                            'nullable' => true,
                        ],
                        'UPC'
                    )

                    ->addColumn(
                        AttributeInterface::COLUMN_EAN,
                        Table::TYPE_TEXT,
                        60,
                        [
                            'nullable' => true,
                        ],
                        'EAN'
                    )->addColumn(
                        AttributeInterface::COLUMN_GTIN,
                        Table::TYPE_TEXT,
                        60,
                        [
                            'nullable' => true,
                        ],
                        'GTIN'
                    )->addColumn(
                        AttributeInterface::COLUMN_TITLE,
                        Table::TYPE_TEXT,
                        60,
                        [
                            'nullable' => false,
                        ],
                        'Title'
                    )->addColumn(
                        AttributeInterface::COLUMN_DESCRIPTION,
                        Table::TYPE_TEXT,
                        60,
                        [
                            'nullable' => false,
                        ],
                        'Description'
                    )->addColumn(
                        AttributeInterface::COLUMN_BRAND,
                        Table::TYPE_TEXT,
                        60,
                        [
                            'nullable' => false,
                        ],
                        'Brand'
                    )->addColumn(
                        AttributeInterface::COLUMN_MANUFACTURER,
                        Table::TYPE_TEXT,
                        60,
                        [
                            'nullable' => false,
                        ],
                        'Manufacturer'
                    )->addColumn(
                        AttributeInterface::COLUMN_MODEL,
                        Table::TYPE_TEXT,
                        60,
                        [
                            'nullable' => false,
                        ],
                        'Model'
                    )->addColumn(
                        AttributeInterface::COLUMN_ADDITIONAL_ATTRIBUTES,
                        Table::TYPE_TEXT,
                        '2M',
                        [
                            'nullable' => true,
                        ],
                        'Additional Attributes'
                    )->addColumn(
                        AttributeInterface::COLUMN_UNITS,
                        Table::TYPE_TEXT,
                        '2M',
                        [
                            'nullable' => true,
                        ],
                        'Units'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Attribute::NAME),
                            AttributeInterface::COLUMN_ATTRIBUTE_RELATION_ID,
                            $installer->getTable(Strategy::NAME),
                            StrategyInterface::COLUMN_ID
                        ),
                        AttributeInterface::COLUMN_ATTRIBUTE_RELATION_ID,
                        $installer->getTable(Strategy::NAME),
                        StrategyInterface::COLUMN_ID,
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            $tableName,
                            [
                                AttributeInterface::COLUMN_ATTRIBUTE_RELATION_ID,
                            ],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        [
                            AttributeInterface::COLUMN_ATTRIBUTE_RELATION_ID,
                        ],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Amazon Strategy Atribute');
                $installer->getConnection()->createTable($table);
            }

            /**
             * Updating `ced_amazon_profile` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(Profile::NAME))) {
                /**
                 * Adding column 'type'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_TYPE
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_TYPE,
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => '255',
                            'comment' => 'Type',
                            'after' => Profile::COLUMN_STORE_ID
                        ]
                    );
                }

                /**
                 * Adding column 'auto_assignment'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_AUTO_ASSIGNMENT
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_AUTO_ASSIGNMENT,
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'comment' => 'Auto Assignment',
                            'default' => '0',
                            'after' => Profile::COLUMN_STORE_ID
                        ]
                    );
                }

                /**
                 * Adding column 'strategy'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_STRATEGY
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STRATEGY,
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'comment' => 'Strategy',
                            'default' => '0',
                            'after' => Profile::COLUMN_STORE_ID
                        ]
                    );
                }

                /**
                 * Adding column 'strategy_assignment'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_STRATEGY_ASSIGNMENT
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STRATEGY_ASSIGNMENT,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'comment' => 'Strategy Assignment',
                            'unsigned' => true,
                            'nullable' => true,
                            'after' => Profile::COLUMN_AUTO_ASSIGNMENT
                        ]
                    );

                    /**
                     * Adding foreign key
                     */
                    $installer->getConnection()->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Profile::NAME),
                            Profile::COLUMN_STRATEGY_ASSIGNMENT,
                            $installer->getTable(Strategy::NAME),
                            Strategy::COLUMN_ID
                        ),
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STRATEGY_ASSIGNMENT,
                        $installer->getTable(Strategy::NAME),
                        Strategy::COLUMN_ID,
                        Table::ACTION_SET_NULL
                    );
                }

                /**
                 * Adding column 'strategy_inventory'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_STRATEGY_INVENTORY
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STRATEGY_INVENTORY,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'comment' => 'Strategy Inventory',
                            'unsigned' => true,
                            'nullable' => true,
                            'after' => Profile::COLUMN_STORE_ID
                        ]
                    );

                    /**
                     * Adding foreign key
                     */
                    $installer->getConnection()->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Profile::NAME),
                            Profile::COLUMN_STRATEGY_INVENTORY,
                            $installer->getTable(Strategy::NAME),
                            Strategy::COLUMN_ID
                        ),
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STRATEGY_INVENTORY,
                        $installer->getTable(Strategy::NAME),
                        Strategy::COLUMN_ID,
                        Table::ACTION_SET_NULL
                    );
                }

                /**
                 * Adding column 'strategy_attribute'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_STRATEGY_ATTRIBUTE
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STRATEGY_ATTRIBUTE,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'comment' => 'Strategy Attribute',
                            'unsigned' => true,
                            'nullable' => true,
                            'after' => Profile::COLUMN_STORE_ID
                        ]
                    );

                    /**
                     * Adding foreign key
                     */
                    $installer->getConnection()->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Profile::NAME),
                            Profile::COLUMN_STRATEGY_ATTRIBUTE,
                            $installer->getTable(Strategy::NAME),
                            Strategy::COLUMN_ID
                        ),
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STRATEGY_ATTRIBUTE,
                        $installer->getTable(Strategy::NAME),
                        Strategy::COLUMN_ID,
                        Table::ACTION_SET_NULL
                    );
                }

                /**
                 * Adding column 'strategy_price'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_STRATEGY_PRICE
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STRATEGY_PRICE,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'comment' => 'Strategy Price',
                            'unsigned' => true,
                            'nullable' => true,
                            'after' => Profile::COLUMN_STORE_ID
                        ]
                    );

                    /**
                     * Adding foreign key
                     */
                    $installer->getConnection()->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Profile::NAME),
                            Profile::COLUMN_STRATEGY_PRICE,
                            $installer->getTable(Strategy::NAME),
                            Strategy::COLUMN_ID
                        ),
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STRATEGY_PRICE,
                        $installer->getTable(Strategy::NAME),
                        Strategy::COLUMN_ID,
                        Table::ACTION_SET_NULL
                    );
                }

                /**
                 * Adding column 'strategy_shipping'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Profile::NAME),
                    Profile::COLUMN_STRATEGY_SHIPPING
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STRATEGY_SHIPPING,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'comment' => 'Strategy Shipping',
                            'unsigned' => true,
                            'nullable' => true,
                            'after' => Profile::COLUMN_STORE_ID
                        ]
                    );

                    /**
                     * Adding foreign key
                     */
                    $installer->getConnection()->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Profile::NAME),
                            Profile::COLUMN_STRATEGY_SHIPPING,
                            $installer->getTable(Strategy::NAME),
                            Strategy::COLUMN_ID
                        ),
                        $installer->getTable(Profile::NAME),
                        Profile::COLUMN_STRATEGY_SHIPPING,
                        $installer->getTable(Strategy::NAME),
                        Strategy::COLUMN_ID,
                        Table::ACTION_SET_NULL
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '0.2.1', '<')) {
            if (!$installer->getConnection()->isTableExists(
                $installer->getTable(Product::NAME)
            )) {
                /**
                 * Create table 'ced_amazon_product'
                 */
                $tableName = $installer->getTable(Product::NAME);
                $table = $installer->getConnection()
                    ->newTable($tableName)
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
                        Product::COLUMN_RELATION_ID,
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Relation Id'
                    )
                    ->addColumn(
                        Product::COLUMN_ASIN,
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => true],
                        'ASIN'
                    )
                    ->addColumn(
                        Product::COLUMN_STATUS,
                        Table::TYPE_TEXT,
                        '1M',
                        ['nullable' => true],
                        'Status'
                    )
                    ->addColumn(
                        Product::COLUMN_UPDATE_LOG,
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => true],
                        'Update Log'
                    )
                    ->addColumn(
                        Product::COLUMN_INVENTORY_LOG,
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => true],
                        'Inventory Log'
                    )
                    ->addColumn(
                        Product::COLUMN_PRICE_LOG,
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => true],
                        'Price Log'
                    )
                    ->addColumn(
                        Product::COLUMN_VALIDATION_ERRORS,
                        Table::TYPE_TEXT,
                        '2M',
                        ['nullable' => true],
                        'Validation Errors'
                    )
                    ->addColumn(
                        Product::COLUMN_FEED_ERRORS,
                        Table::TYPE_TEXT,
                        '2M',
                        ['nullable' => true],
                        'Feed Errors'
                    )
                    ->addColumn(
                        Product::COLUMN_TITLE,
                        Table::TYPE_BOOLEAN,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Title Matched'
                    )
                    ->addColumn(
                        Product::COLUMN_BRAND,
                        Table::TYPE_BOOLEAN,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Brand Matched'
                    )
                    ->addColumn(
                        Product::COLUMN_MANUFACTURER,
                        Table::TYPE_BOOLEAN,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Manufacturer Matched'
                    )
                    ->addColumn(
                        Product::COLUMN_AUTO_ASSIGNED,
                        Table::TYPE_BOOLEAN,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Auto Assigned'
                    )
                    ->addColumn(
                        Product::COLUMN_MANUALLY_ASSIGNED,
                        Table::TYPE_BOOLEAN,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Manually Assigned'
                    )
                    ->addColumn(
                        Product::COLUMN_MARKETPLACE_ID,
                        Table::TYPE_TEXT,
                        null,
                        [
                            'nullable' => true,
                        ],
                        'MarketPlace Id'
                    )
                    ->addColumn(
                        Product::COLUMN_ACCOUNT_ID,
                        Table::TYPE_TEXT,
                        null,
                        [
                            'nullable' => true,
                        ],
                        'Account Id'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(Product::NAME),
                            Product::COLUMN_RELATION_ID,
                            $installer->getTable(Profile\Product::NAME),
                            Profile\Product::COLUMN_ID
                        ),
                        Product::COLUMN_RELATION_ID,
                        $installer->getTable(Profile\Product::NAME),
                        Profile\Product::COLUMN_ID,
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            $tableName,
                            [
                                Product::COLUMN_RELATION_ID,
                            ],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        [
                            Product::COLUMN_RELATION_ID,
                        ],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Amazon Product');
                $installer->getConnection()->createTable($table);
            }

            /**
             * Create Search Product Table
             */
            if (!$installer->getConnection()->isTableExists(
                $installer->getTable(\Ced\Amazon\Model\Search\Product::NAME)
            )) {
                /**
                 * Create table 'ced_amazon_search_product'
                 */
                $tableName = $installer->getTable(\Ced\Amazon\Model\Search\Product::NAME);
                $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        ProductInterface::COLUMN_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Id'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_RELATION_ID,
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Relation Id'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_IMAGE,
                        Table::TYPE_TEXT,
                        500,
                        ['nullable' => true],
                        'Image'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_ASIN,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'unsigned' => true,
                            'nullable' => false
                        ],
                        'ASIN'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_UPC,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'UPC'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_EAN,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'EAN'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_GTIN,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'GTIN'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_BRAND,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'Brand'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_MODEL,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'Model'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_TITLE,
                        Table::TYPE_TEXT,
                        1000,
                        [
                            'nullable' => true,
                        ],
                        'Title'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_MANUFACTURER,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'manufacturer'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_DESCRIPTION,
                        Table::TYPE_TEXT,
                        "2M",
                        [
                            'nullable' => true,
                        ],
                        'Description'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_MARKETPLACE_ID,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'MarketPlace Id'
                    )
                    ->addColumn(
                        ProductInterface::COLUMN_RESPONSE,
                        Table::TYPE_TEXT,
                        '2M',
                        [
                            'nullable' => false,
                        ],
                        'Response'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(\Ced\Amazon\Model\Search\Product::NAME),
                            \Ced\Amazon\Model\Search\Product::COLUMN_RELATION_ID,
                            $installer->getTable(Profile\Product::NAME),
                            Profile\Product::COLUMN_ID
                        ),
                        \Ced\Amazon\Model\Search\Product::COLUMN_RELATION_ID,
                        $installer->getTable(Profile\Product::NAME),
                        Profile\Product::COLUMN_ID,
                        Table::ACTION_CASCADE
                    )->addIndex(
                        $installer->getIdxName(
                            $tableName,
                            [
                                \Ced\Amazon\Model\Search\Product::COLUMN_ASIN,
                            ],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        [
                            \Ced\Amazon\Model\Search\Product::COLUMN_ASIN,
                        ],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    );
                $installer->getConnection()->createTable($table);
            }

            if ($installer->getConnection()->isTableExists($installer->getTable(Order::NAME))) {
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_IS_PRIME
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_IS_PRIME,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'comment' => 'Is Prime Order',
                            'unsigned' => true,
                            'nullable' => false,
                            'default' => 0
                        ]
                    );
                }
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_IS_BUSINESS
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_IS_BUSINESS,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'comment' => 'Is Business Order',
                            'unsigned' => true,
                            'nullable' => false,
                            'default' => 0
                        ]
                    );
                }
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(Order::NAME),
                    Order::COLUMN_IS_PREMIUM
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(Order::NAME),
                        Order::COLUMN_IS_PREMIUM,
                        [
                            'type' => Table::TYPE_INTEGER,
                            'comment' => 'Is Premium Order',
                            'unsigned' => true,
                            'nullable' => false,
                            'default' => 0
                        ]
                    );
                }
            }

//            try {
//                $taxRate = $this->rateFactory->create()->loadByCode('Ced_Amazon');
//                $taxRateId = $taxRate->getId();
//
//                if (!$taxRateId) {
//                    $taxCalculationRate1 = $this->rateFactory->create();
//                    $taxCalculationRate1->setCode("Ced_Amazon ");
//                    $countryId = $this->scopeConfig->getValue(
//                        \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
//                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
//                    );
//                    $taxCalculationRate1->setTaxCountryId($countryId);
//                    $taxCalculationRate1->setTaxRegionId("*");
//                    $taxCalculationRate1->setZipIsRange(0);
//                    $taxCalculationRate1->setTaxPostcode("*");
//                    $taxCalculationRate1->setRate("0");
//                    $result = $this->rate->save($taxCalculationRate1);
//                    $taxRateId = $taxCalculationRate1->getId();
//                }
//
//            } catch (\Exception $e) {
//
//            }
//
//
//            try {
//                $productTaxClassId = $this->taxClassModel->create()->addFieldToFilter('class_type', ['eq' => 'PRODUCT'])->getFirstItem()->getData();
//                $productTaxClassId = isset($productTaxClassId['class_id']) ? $productTaxClassId['class_id'] : 0;
//
//                $customerTaxClassId = $this->taxClassModel->create()->addFieldToFilter('class_type', ['eq' => 'CUSTOMER'])->getFirstItem()->getData();
//                $customerTaxClassId = isset($customerTaxClassId['class_id']) ? $customerTaxClassId['class_id'] : 0;
//
//                $fixtureTaxRule1 = $this->ruleFactory->create();
//                $fixtureTaxRule1->setCode("Ced_Amazon");
//                $fixtureTaxRule1->setPriority(1000);
//                $fixtureTaxRule1->setCustomerTaxClassIds(array($customerTaxClassId));
//                $fixtureTaxRule1->setProductTaxClassIds(array($productTaxClassId));
//                $fixtureTaxRule1->setTaxRateIds(array($taxRateId));
//                $this->rule->save($fixtureTaxRule1);
//            } catch (\Exception $e) {
//
//            }
        }

        $installer->endSetup();
    }
}
