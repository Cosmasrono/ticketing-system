<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ticket_message}}`.
 */
class m240326_000001_create_ticket_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ticket_message}}', [
            'id' => $this->primaryKey(),
            'ticket_id' => $this->integer()->notNull(),
            'sender_id' => $this->integer()->notNull(),
            'recipient_id' => $this->integer()->notNull(),
            'subject' => $this->string()->notNull(),
            'message' => $this->text()->notNull(),
            'sent_at' => $this->integer()->notNull(),
            'read_at' => $this->integer()->null(),
            'admin_viewed' => $this->tinyInteger()->defaultValue(0),
            'message_type' => $this->string()->notNull()->defaultValue('user_message'),
            'is_internal' => $this->tinyInteger()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Add foreign key for ticket_id
        $this->addForeignKey(
            'fk-ticket_message-ticket_id',
            '{{%ticket_message}}',
            'ticket_id',
            '{{%ticket}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add foreign key for sender_id
        $this->addForeignKey(
            'fk-ticket_message-sender_id',
            '{{%ticket_message}}',
            'sender_id',
            '{{%users}}',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        // Add foreign key for recipient_id
        $this->addForeignKey(
            'fk-ticket_message-recipient_id',
            '{{%ticket_message}}',
            'recipient_id',
            '{{%users}}',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        // Create indexes for faster queries
        $this->createIndex(
            'idx-ticket_message-ticket_id',
            '{{%ticket_message}}',
            'ticket_id'
        );

        $this->createIndex(
            'idx-ticket_message-admin_viewed',
            '{{%ticket_message}}',
            'admin_viewed'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign keys first
        $this->dropForeignKey('fk-ticket_message-recipient_id', '{{%ticket_message}}');
        $this->dropForeignKey('fk-ticket_message-sender_id', '{{%ticket_message}}');
        $this->dropForeignKey('fk-ticket_message-ticket_id', '{{%ticket_message}}');

        // Drop indexes
        $this->dropIndex('idx-ticket_message-admin_viewed', '{{%ticket_message}}');
        $this->dropIndex('idx-ticket_message-ticket_id', '{{%ticket_message}}');

        // Drop the table
        $this->dropTable('{{%ticket_message}}');
    }
}