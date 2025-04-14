<?php

use yii\db\Migration;

class m240402_567098_create_ticket_table extends Migration
{
    public function up()
    {
        $this->createTable('ticket', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'created_by' => $this->integer(),
            'developer_id' => $this->integer(),
            'created_at' => $this->integer(),
            'assigned_to' => $this->integer(),
            'escalated_by' => $this->integer(),
            'escalated_to' => $this->integer(),
            'status' => $this->integer()->defaultValue(0),
            'severity_level' => $this->integer(),
            'first_response_at' => $this->integer(),
            'resolution_deadline' => $this->integer(),
            'last_update_at' => $this->integer(),
            'sla_status' => $this->integer(),
            'next_update_due' => $this->integer(),
            'reopen_reason' => $this->text(),
            'escalated_at' => $this->integer(),
            'action' => $this->text(),
            'closed_by' => $this->integer(),
            'assigned_at' => $this->integer(),
            'timer_start' => $this->integer(),
            'closed_at' => $this->integer(),
            'time_taken' => $this->integer(),
            'comments' => $this->text(),
            'user_id' => $this->integer(),
            'company_email' => $this->string(255),
            'company_name' => $this->string(255),
            'escalation_comment' => $this->text(),
            'scheduled_close_at' => $this->integer(),
            'screenshot_url' => $this->string(255),
            'voice_note_url' => $this->string(255),
            'renewal_status' => $this->integer(),
            'renewal_date' => $this->integer(),
            'approved_by' => $this->integer(),
            'developer_name' => $this->string(255),
            'module' => $this->string(255),
            'issue' => $this->text(),
            'response_time' => $this->integer()
        ]);

        // Add indexes
        $this->createIndex('idx-ticket-company_id', 'ticket', 'company_id');
        $this->createIndex('idx-ticket-created_by', 'ticket', 'created_by');
        $this->createIndex('idx-ticket-assigned_to', 'ticket', 'assigned_to');
        $this->createIndex('idx-ticket-status', 'ticket', 'status');
        $this->createIndex('idx-ticket-user_id', 'ticket', 'user_id');
    }

    public function down()
    {
        // Drop indexes first
        $this->dropIndex('idx-ticket-company_id', 'ticket');
        $this->dropIndex('idx-ticket-created_by', 'ticket');
        $this->dropIndex('idx-ticket-assigned_to', 'ticket');
        $this->dropIndex('idx-ticket-status', 'ticket');
        $this->dropIndex('idx-ticket-user_id', 'ticket');

        // Drop the table
        $this->dropTable('ticket');
    }
} 