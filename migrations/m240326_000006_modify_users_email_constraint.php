<?php

use yii\db\Migration;

class m240326_000006_modify_users_email_constraint extends Migration
{
    public function safeUp()
    {
        // Check if index exists before trying to drop it
        $indexName = 'idx-users-company_email';
        $tableName = 'users';
        $schema = $this->db->getSchema();
        $table = $schema->getTableSchema($tableName);
        $exists = false;
        
        if ($table !== null) {
            foreach ($schema->findTableIndexes($tableName) as $name => $index) {
                if (strtolower($name) === strtolower($indexName)) {
                    $exists = true;
                    break;
                }
            }
        }
        
        if ($exists) {
            $this->dropIndex('idx-users-company_email', 'users');
        }
        
        // Proceed with creating new index or other operations
        $this->createIndex('idx-users-company_email-unique', 'users', 'company_email', true);
    }

    public function safeDown()
    {
        // Drop the new unique index
        $this->dropIndex('idx-users-company_email-unique', 'users');
        
        // Recreate the old non-unique index
        $this->createIndex('idx-users-company_email', 'users', 'company_email');
    }
} 