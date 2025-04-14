<?php

use yii\db\Migration;

class m240201_234789_modify_subscription_level extends Migration
{
    public function safeUp()
    {
        // For SQL Server, we need to drop any constraints first
        $this->execute('ALTER TABLE company ALTER COLUMN subscription_level VARCHAR(255) NULL');
    }

    public function safeDown()
    {
        // Revert back to not null
        $this->execute('ALTER TABLE company ALTER COLUMN subscription_level VARCHAR(255) NOT NULL');
    }
} 