<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%users}}`.
 */
class m240100_074852_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(),
            'name' => $this->string()->notNull(),
            'company_name' => $this->string()->notNull(),
            'company_email' => $this->string()->notNull(),
            'password_hash' => $this->string()->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'role' => $this->integer(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'is_verified' => $this->boolean()->defaultValue(false),
            'first_login' => $this->boolean()->defaultValue(true),
            'modules' => $this->text(),
            'password_reset_token' => $this->string(),
            'verification_token' => $this->string(),
            'token_created_at' => $this->integer(),
            'email_verified' => $this->boolean()->defaultValue(false)
        ]);

        // Add indexes for better performance
        $this->createIndex(
            'idx-users-company_id',
            '{{%users}}',
            'company_id'
        );

        $this->createIndex(
            'idx-users-company_email',
            '{{%users}}',
            'company_email',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-users-company_email', '{{%users}}');
        $this->dropIndex('idx-users-company_id', '{{%users}}');
        $this->dropTable('{{%users}}');
    }
}
