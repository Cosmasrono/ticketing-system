<?php

use yii\db\Migration;

class m240326_000006_modify_users_email_constraint extends Migration
{
    public function safeUp()
    {
        $tableName = 'users';
        
        // Check if table exists
        if ($this->db->getTableSchema($tableName) === null) {
            echo "Table '$tableName' does not exist. Skipping migration.\n";
            return true;
        }
        
        // Try to drop the old index if it exists (use try-catch for SQL Server)
        try {
            $this->dropIndex('idx-users-company_email', $tableName);
        } catch (\Exception $e) {
            echo "Index 'idx-users-company_email' does not exist or already dropped.\n";
        }
        
        // Create new unique index
        try {
            $this->createIndex('idx-users-company_email-unique', $tableName, 'company_email', true);
        } catch (\Exception $e) {
            echo "Index 'idx-users-company_email-unique' may already exist.\n";
        }
    }

    public function safeDown()
    {
        $tableName = 'users';
        
        if ($this->db->getTableSchema($tableName) === null) {
            return true;
        }
        
        // Drop the new unique index
        try {
            $this->dropIndex('idx-users-company_email-unique', $tableName);
        } catch (\Exception $e) {
            // Index may not exist
        }
        
        // Recreate the old non-unique index
        try {
            $this->createIndex('idx-users-company_email', $tableName, 'company_email');
        } catch (\Exception $e) {
            // Index may already exist
        }
    }
}