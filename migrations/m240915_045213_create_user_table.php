<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */ 
    
    class m240915_045213_create_user_table extends Migration
    {
        public function safeUp()
        {
            $table = '{{%user}}';
    
            // Check if the table exists, if not, create it
            if ($this->db->schema->getTableSchema($table, true) === null) {
                $this->createTable($table, [
                    'id' => $this->primaryKey(),
                    'user_id' => $this->integer(),
                    'name' => $this->string(),
                    'company_name' => $this->string(),
                    'company_email' => $this->string()->unique(),
                    'password_hash' => $this->string(),
                    'authKey' => $this->string(),
                    'accessToken' => $this->string(),
                    'created_at' => $this->integer(),
                    'updated_at' => $this->integer(),
                    'auth_key' => $this->string(32),
                    'role' => $this->string(),
                    'is_verified' => $this->boolean()->defaultValue(false),
                    'status' => $this->smallInteger()->defaultValue(10),
                    'password_reset_token' => $this->string()->unique(),
                    'verification_token' => $this->string()->unique(),
                ]);
            } else {
                // Table exists, check and add missing columns
                $columns = [
                    'user_id' => $this->integer(),
                    'name' => $this->string(),
                    'company_name' => $this->string(),
                    'company_email' => $this->string()->unique(),
                    'password_hash' => $this->string(),
                    'authKey' => $this->string(),
                    'accessToken' => $this->string(),
                    'created_at' => $this->integer(),
                    'updated_at' => $this->integer(),
                    'auth_key' => $this->string(32),
                    'role' => $this->string(),
                    'is_verified' => $this->boolean()->defaultValue(false),
                    'status' => $this->smallInteger()->defaultValue(10),
                    'password_reset_token' => $this->string()->unique(),
                    'verification_token' => $this->string()->unique(),
                ];
    
                foreach ($columns as $column => $type) {
                    if ($this->db->schema->getTableSchema($table, true)->getColumn($column) === null) {
                        $this->addColumn($table, $column, $type);
                    }
                }
            }
    
            // Ensure indexes
            $this->createIndex('idx-user-company_email', $table, 'company_email', true);
            $this->createIndex('idx-user-password_reset_token', $table, 'password_reset_token', true);
            $this->createIndex('idx-user-verification_token', $table, 'verification_token', true);
        }
    
        public function safeDown()
        {
            echo "m240915_045213_create_user_table cannot be reverted.\n";
            return false;
        }
    }
