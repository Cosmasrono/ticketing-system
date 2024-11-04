<?php

use yii\db\Migration;

class m231104_082500_add_password_reset_columns extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%user}}', 'password_reset_token');
        $this->addColumn('{{%user}}', 'password_reset_token', $this->string()->unique());
        $this->addColumn('{{%user}}', 'password_reset_token_created_at', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'password_reset_token_created_at');
        $this->addColumn('{{%user}}', 'password_reset_token', $this->string()->unique());
    }
} 