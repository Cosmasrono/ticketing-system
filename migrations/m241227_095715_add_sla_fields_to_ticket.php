<?php

use yii\db\Migration;

/**
 * Class m241227_095715_add_sla_fields_to_ticket
 */
class m241227_095715_add_sla_fields_to_ticket extends Migration
 
{
    
        public function safeUp()
        {
            // Add SLA fields to existing ticket table
            $this->addColumn('ticket', 'severity_level', $this->integer()->notNull()->defaultValue(4)->after('status'));
            $this->addColumn('ticket', 'first_response_at', $this->dateTime()->null()->after('severity_level'));
            $this->addColumn('ticket', 'resolution_deadline', $this->dateTime()->null()->after('first_response_at'));
            $this->addColumn('ticket', 'last_update_at', $this->dateTime()->null()->after('resolution_deadline'));
            $this->addColumn('ticket', 'sla_status', $this->string(20)->defaultValue('within')->after('last_update_at'));
            $this->addColumn('ticket', 'next_update_due', $this->dateTime()->null()->after('sla_status'));
    
            // Create indexes for better performance
            $this->createIndex('idx-ticket-severity', 'ticket', 'severity_level');
            $this->createIndex('idx-ticket-sla_status', 'ticket', 'sla_status');
        }
    
        public function safeDown()
        {
            // Remove indexes
            $this->dropIndex('idx-ticket-severity', 'ticket');
            $this->dropIndex('idx-ticket-sla_status', 'ticket');
    
            // Remove columns
            $this->dropColumn('ticket', 'severity_level');
            $this->dropColumn('ticket', 'first_response_at');
            $this->dropColumn('ticket', 'resolution_deadline');
            $this->dropColumn('ticket', 'last_update_at');
            $this->dropColumn('ticket', 'sla_status');
            $this->dropColumn('ticket', 'next_update_due');
        }
    }