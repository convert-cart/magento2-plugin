<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     * Upgrades the database schema for the Convertcart Analytics module.
     *
     * @param SchemaSetupInterface   $setup   Schema setup
     * @param ModuleContextInterface $context Module context
     *
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $conn = $setup->getConnection();
        if ($setup->getConnection()->isTableExists('convertcart_sync_activity') != true) {
            $tableName = $setup->getTable('convertcart_sync_activity');
            if ($conn->isTableExists($tableName) != true) {

                $table = $conn->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned'=>true,'nullable'=>false,'auto_increment' => true,'primary'=>true]
                    )
                    ->addColumn(
                        'item_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable'=>false]
                    )
                    ->addColumn(
                        'type',
                        Table::TYPE_TEXT,
                        55,
                        ['nullable'=>false]
                    )
                    ->addColumn(
                        'created_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]
                    )
                    ->setOption('charset', 'utf8');
                $conn->createTable($table);
            }
        }
        $setup->endSetup();
    }
}
