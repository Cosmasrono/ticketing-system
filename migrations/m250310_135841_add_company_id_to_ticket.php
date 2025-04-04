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
        $this->addColumn('ticket', 'company_id', $this->integer()->defaultValue(null)->after('id'));
        
        // Add foreign key
        $this->createIndex(
            'idx-ticket-company_id',
            'ticket',
            'company_id'
        );

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
        $this->dropForeignKey('fk-ticket-company_id', 'ticket');
        $this->dropIndex('idx-ticket-company_id', 'ticket');
        $this->dropColumn('ticket', 'company_id');
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
