<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ticket}}`.
 */
class m240919_121138_create_ticket_table extends Migration
{
    
    public function up()
    {
        $this->createTable('ticket', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'status' => "ENUM('pending', 'in_progress', 'resolved', 'cancelled') NOT NULL DEFAULT 'pending'",
            'company_email' => $this->string()->notNull(),
            'created_by' => $this->integer(),
            'developer_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Add foreign key for created_by
        $this->addForeignKey(
            'fk-ticket-created_by',
            'ticket',
            'created_by',
            'user',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Add foreign key for developer_id
        $this->addForeignKey(
            'fk-ticket-developer_id',
            'ticket',
            'developer_id',
            'developer',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // Drop foreign keys
        $this->dropForeignKey('fk-ticket-created_by', 'ticket');
        $this->dropForeignKey('fk-ticket-developer_id', 'ticket');
        
        // Drop the ticket table
        $this->dropTable('ticket');
    }
}