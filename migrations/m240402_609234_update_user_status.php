<?php

use yii\db\Migration;

class m240402_609234_update_user_status extends Migration
{
    public function safeUp()
    {
        // Update existing status values
        $this->execute('UPDATE users SET status = 1 WHERE status = 10');
        $this->execute('UPDATE users SET status = 0 WHERE status != 1');
    }

    public function safeDown()
    {
        // Revert status values if needed
        $this->execute('UPDATE users SET status = 10 WHERE status = 1');
        $this->execute('UPDATE users SET status = 9 WHERE status = 0');
    }
} 