<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%admin_notifications}}`.
 */
class m241114_111123_create_admin_notifications_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('admin_notifications', [
            'id' => $this->primaryKey(),
            'company_email' => $this->string(255)->notNull(),
            'is_active' => $this->tinyInteger(1)->defaultValue(1),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Add index on company_email
        $this->createIndex(
            'idx-admin_notifications-company_email',
            'admin_notifications',
            'company_email'
        );

        // Insert the admin's company email
        $this->insert('admin_notifications', [
            'company_email' => 'ccosmas001@gmail.com', // Replace with your actual admin company email
            'is_active' => 1
        ]);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-admin_notifications-company_email', 'admin_notifications');
        $this->dropTable('admin_notifications');
    }
}