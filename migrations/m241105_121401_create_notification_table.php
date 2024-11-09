<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notification}}`.
 */
class m241105_121401_create_notification_table extends Migration
{ 
        public function safeUp()
        {
            $this->createTable('notification', [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer()->notNull(),
                'title' => $this->string(255)->notNull(),
                'message' => $this->text()->notNull(),
                'type' => $this->string(50)->notNull(),
                'status' => $this->string(50)->notNull()->defaultValue('unread'),
                'reference_id' => $this->integer(),
                'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            ]);
    
            // Add foreign key
            $this->addForeignKey(
                'fk-notification-user_id',
                'notification',
                'user_id',
                'user',
                'id',
                'CASCADE',
                'CASCADE'
            );
    
            // Create index for user_id
            $this->createIndex(
                'idx-notification-user_id',
                'notification',
                'user_id'
            );
        }
    
        public function safeDown()
        {
            // Drop foreign key first
            $this->dropForeignKey('fk-notification-user_id', 'notification');
            
            // Drop index
            $this->dropIndex('idx-notification-user_id', 'notification');
            
            // Drop the table
            $this->dropTable('notification');
        }
    }