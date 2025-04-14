<?php

use yii\db\Migration;

/**
 * Class m250310_135841_add_company_id_to_ticket
 */
class m250310_135841_add_company_id_to_ticket extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if column exists
        $table = $this->db->getTableSchema('{{%ticket}}');
        if ($table === null || !isset($table->columns['company_id'])) {
            $this->addColumn('{{%ticket}}', 'company_id', $this->integer()->null()->defaultValue(null));
        }

        // Check if index exists using SQL Server specific query
        $indexExists = $this->db->createCommand("
            SELECT COUNT(*) FROM sys.indexes 
            WHERE name = 'idx-ticket-company_id' 
            AND object_id = OBJECT_ID('ticket')
        ")->queryScalar();

        if (!$indexExists) {
            $this->createIndex('idx-ticket-company_id', '{{%ticket}}', 'company_id');
        }

        // Add foreign key
        $this->addForeignKey(
            'fk-ticket-company_id',
            'ticket',
            'company_id',
            'company',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop index if exists
        $this->dropIndex('idx-ticket-company_id', '{{%ticket}}');
        
        // Drop column if exists
        $table = $this->db->getTableSchema('{{%ticket}}');
        if ($table !== null && isset($table->columns['company_id'])) {
            $this->dropForeignKey('fk-ticket-company_id', 'ticket');
            $this->dropColumn('{{%ticket}}', 'company_id');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250310_135841_add_company_id_to_ticket cannot be reverted.\n";

        return false;
    }
    */
}
