<?php

use yii\db\Migration;

/**
 * Class m241028_083614_add_escalated_at_to_ticket
 */
class m241028_083614_add_escalated_at_to_ticket extends Migration
{
  
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add escalated_at column
        $this->addColumn('ticket', 'escalated_at', $this->dateTime()->null()->after('status'));

        // Update status column to allow escalated status
        $this->alterColumn('ticket', 'status', $this->string(20)->notNull()->defaultValue('pending'));

        // Add index for better performance
        $this->createIndex(
            'idx-ticket-escalated_at',
            'ticket',
            'escalated_at'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop index first
        $this->dropIndex(
            'idx-ticket-escalated_at',
            'ticket'
        );

        // Drop the column
        $this->dropColumn('ticket', 'escalated_at');
    }
}