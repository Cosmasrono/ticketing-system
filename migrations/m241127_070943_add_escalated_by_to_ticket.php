<?php

use yii\db\Migration;

/**
 * Class m241127_070943_add_escalated_by_to_ticket
 */
class m241127_070943_add_escalated_by_to_ticket extends Migration
{ 
        /**
         * {@inheritdoc}
         */
        public function safeUp()
        {
            // Check if escalated_by column exists before adding it
            if (!$this->getDb()->getSchema()->getTableSchema('{{%ticket}}')->getColumn('escalated_by')) {
                $this->addColumn('{{%ticket}}', 'escalated_by', $this->integer()->null()->after('assigned_to'));
                
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
            
            // Check if escalated_at column exists before adding it
            if (!$this->getDb()->getSchema()->getTableSchema('{{%ticket}}')->getColumn('escalated_at')) {
                $this->addColumn('{{%ticket}}', 'escalated_at', $this->timestamp()->null()->after('escalated_by'));
            }
        }
    
        /**
         * {@inheritdoc}
         */
        public function safeDown()
        {
            // Drop foreign key first
            $this->dropForeignKey('fk-ticket-escalated_by', '{{%ticket}}');
            
            // Drop columns
            $this->dropColumn('{{%ticket}}', 'escalated_by');
            $this->dropColumn('{{%ticket}}', 'escalated_at');
        }
    }