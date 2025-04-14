<?php

use yii\db\Migration;

class m240203_070000_create_users_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('users', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(),
            'name' => $this->string()->notNull(),
            'company_name' => $this->string(),
            'company_email' => $this->string()->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'auth_key' => $this->string(32),
            'role' => $this->string()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'is_verified' => $this->boolean()->defaultValue(0),
            'first_login' => $this->boolean()->defaultValue(1),
            'modules' => $this->text(), // Assuming this stores JSON or serialized data
            'password_reset_token' => $this->string()->unique(),
            'verification_token' => $this->string()->unique(),
            'token_created_at' => $this->timestamp()->null(),
            'email_verified' => $this->boolean()->defaultValue(0),
        ]);

        // Add index for company_id
        $this->createIndex(
            'idx-users-company_id',
            'users',
            'company_id'
        );

        // Add foreign key for company_id if the company table exists
        if ($this->db->schema->getTableSchema('company') !== null) {
            $this->addForeignKey(
                'fk-users-company_id',
                'users',
                'company_id',
                'company',
                'id',
                'CASCADE'
            );
        }
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('users') !== null) {
            if ($this->db->schema->getTableSchema('company') !== null) {
                $this->dropForeignKey('fk-users-company_id', 'users');
            }
            $this->dropIndex('idx-users-company_id', 'users');
            $this->dropTable('users');
        }
    }
} 