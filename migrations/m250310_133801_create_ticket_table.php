<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ticket}}`.
 */
class m250310_133801_create_ticket_table extends Migration
{
    public function safeUp()
    {
        if (!$this->db->getTableSchema('{{%ticket}}', true)) {
            $this->createTable('{{%ticket}}', [
                'id' => $this->primaryKey(),
                'title' => $this->string(255)->notNull(),
                'description' => $this->text()->null()->defaultValue(null),
                'created_by' => $this->integer()->null()->defaultValue(null),
                'developer_id' => $this->integer()->null()->defaultValue(null),
                'created_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
                'assigned_to' => $this->integer()->null()->defaultValue(null),
                'escalated_by' => $this->integer()->null()->defaultValue(null),
                'escalated_to' => $this->integer()->null()->defaultValue(null),
                'status' => "nvarchar(50) DEFAULT 'pending'",
                'severity_level' => $this->integer()->notNull()->defaultValue(4),
                'first_response_at' => $this->datetime()->defaultValue(null),
                'resolution_deadline' => $this->datetime()->defaultValue(null),
                'last_update_at' => $this->datetime()->defaultValue(null),
                'sla_status' => $this->string(20)->defaultValue('within'),
                'next_update_due' => $this->datetime()->defaultValue(null),
                'reopen_reason' => $this->text()->defaultValue(null),
                'escalated_at' => $this->datetime()->defaultValue(null),
                'action' => $this->string(255)->defaultValue('reopen'),
                'closed_by' => $this->integer()->defaultValue(null),
                'assigned_at' => $this->datetime()->defaultValue(null),
                'timer_start' => $this->datetime()->defaultValue(null),
                'closed_at' => $this->datetime()->defaultValue(null),
                'time_taken' => $this->string(255)->defaultValue('Reviewing'),
                'comments' => $this->text()->defaultValue(null),
                'user_id' => $this->integer()->defaultValue(null),
                'company_email' => $this->string(255)->notNull(),
                'company_name' => $this->string(255)->defaultValue(null),
                'escalation_comment' => $this->text()->defaultValue(null)->comment('Comment provided when escalating the ticket'),
                'scheduled_close_at' => $this->datetime()->defaultValue(null),
                'screenshotUrl' => $this->string(255)->defaultValue(null),
                'voice_note_url' => $this->string(255)->defaultValue(null),
                'renewal_status' => $this->string(255)->defaultValue(null),
                'renewal_date' => $this->datetime()->defaultValue(null),
                'approved_by' => $this->integer()->defaultValue(null),
                'developer_name' => $this->string(255)->defaultValue(null),
                'module' => $this->string(255)->defaultValue(null),
                'issue' => $this->string(255)->defaultValue(null),
                'screenshot_url' => $this->string(255)->defaultValue(null),
                'response_time' => $this->float()->defaultValue(null),
            ]);

            // Add indexes for better performance
            $this->createIndex('idx-ticket-created_by', 'ticket', 'created_by');
            $this->createIndex('idx-ticket-developer_id', 'ticket', 'developer_id');
            $this->createIndex('idx-ticket-assigned_to', 'ticket', 'assigned_to');
            $this->createIndex('idx-ticket-user_id', 'ticket', 'user_id');

            // Add foreign key constraints
            $this->addForeignKey(
                'fk-ticket-created_by',
                'ticket',
                'created_by',
                'users',
                'id',
                'SET NULL',
                'CASCADE'
            );

            $this->addForeignKey(
                'fk-ticket-developer_id',
                'ticket',
                'developer_id',
                'users',
                'id',
                'SET NULL',
                'CASCADE'
            );

            $this->addForeignKey(
                'fk-ticket-assigned_to',
                'ticket',
                'assigned_to',
                'users',
                'id',
                'SET NULL',
                'CASCADE'
            );

            $this->addForeignKey(
                'fk-ticket-user_id',
                'ticket',
                'user_id',
                'users',
                'id',
                'SET NULL',
                'CASCADE'
            );

            // Set the charset and collation
            $this->execute('ALTER TABLE ticket CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

            // Add CHECK constraint for status values
            $this->execute("ALTER TABLE [ticket] ADD CONSTRAINT [CK_ticket_status] CHECK 
                (status IN ('pending','approved','cancelled','assigned','escalated','closed','reopen','reassigned'))");
        }
    }

    public function safeDown()
    {
        // Drop foreign keys first
        $this->dropForeignKey('fk-ticket-created_by', 'ticket');
        $this->dropForeignKey('fk-ticket-developer_id', 'ticket');
        $this->dropForeignKey('fk-ticket-assigned_to', 'ticket');
        $this->dropForeignKey('fk-ticket-user_id', 'ticket');

        // Drop indexes
        $this->dropIndex('idx-ticket-created_by', 'ticket');
        $this->dropIndex('idx-ticket-developer_id', 'ticket');
        $this->dropIndex('idx-ticket-assigned_to', 'ticket');
        $this->dropIndex('idx-ticket-user_id', 'ticket');

        // Drop the table
        $this->dropTable('{{%ticket}}');
    }
}