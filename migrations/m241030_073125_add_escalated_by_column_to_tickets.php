<?php

use yii\db\Migration;

/**
 * Class m241030_073125_add_escalated_by_column_to_tickets
 */
class m241030_073125_add_escalated_by_column_to_tickets extends Migration
{ 
        public function safeUp()
        {
            $this->addColumn('{{%ticket}}', 'escalated_by', $this->integer()->null());
            
            // Add foreign key
            $this->addForeignKey(
                'fk-ticket-escalated_by',
                '{{%ticket}}',
                'escalated_by',
                '{{%user}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }
    
        public function safeDown()
        {
            $this->dropForeignKey('fk-ticket-escalated_by', '{{%ticket}}');
            $this->dropColumn('{{%ticket}}', 'escalated_by');

    }
}
