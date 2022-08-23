<?php
namespace Mocean\Sms\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        
        if ($connection->tableColumnExists('sales_order', 'is_mocean_send') === false) {
             $orderTable = $installer->getTable('sales_order');
             $connection->addColumn(
                 $orderTable,
                 'is_mocean_send',
                 [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length'    => 1,
                    'comment' => 'Flag for Moceanapi is sent on new order'
                 ]
             );
        }
        $installer->endSetup();
    }
}
