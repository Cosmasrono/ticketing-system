<?php

use yii\db\Migration;

/**
 * Class m241127_093935_add_scheduled_close_at_to_ticket
 */
class m241127_093935_add_scheduled_close_at_to_ticket extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add scheduled_close_at column
        $this->addColumn('{{%ticket}}', 'scheduled_close_at', $this->dateTime()->null());
        
        // Add index for better query performance
        $this->createIndex(
            'idx-ticket-scheduled_close_at',
            '{{%ticket}}',
            'scheduled_close_at'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop index first
        $this->dropIndex(
            'idx-ticket-scheduled_close_at',
            '{{%ticket}}'
        );
        
        // Drop column
        $this->dropColumn('{{%ticket}}', 'scheduled_close_at');
    }
}