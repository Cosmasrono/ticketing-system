<?php

use yii\db\Migration;

/**
 * Class m241127_075256_add_escalated_to_to_ticket
 */
class m241127_075256_add_escalated_to_to_ticket extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if escalated_to column exists before adding it
        if (!$this->getDb()->getSchema()->getTableSchema('{{%ticket}}')->getColumn('escalated_to')) {
            $this->addColumn('{{%ticket}}', 'escalated_to', $this->integer()->null()->after('escalated_by'));
            
            // Add foreign key
            $this->addForeignKey(
                'fk-ticket-escalated_to',
                '{{%ticket}}',
                'escalated_to',
                '{{%user}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ticket-escalated_to', '{{%ticket}}');
        $this->dropColumn('{{%ticket}}', 'escalated_to');
    }
}