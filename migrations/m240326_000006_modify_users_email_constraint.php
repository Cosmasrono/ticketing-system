<?php

use yii\db\Migration;

class m240326_000006_modify_users_email_constraint extends Migration
{
    public function safeUp()
    {
        // Drop the unique index if it exists
        $this->dropIndex('idx-users-company_email', 'users');
        
        // Create a non-unique index
        $this->createIndex('idx-users-company_email', 'users', 'company_email');
    }

    public function safeDown()
    {
        // Drop the non-unique index
        $this->dropIndex('idx-users-company_email', 'users');
        
        // Recreate the unique index
        $this->createIndex('idx-users-company_email', 'users', 'company_email', true);
    }
} 