<?php

use yii\db\Migration;

class m240915_150000_add_auth_key_column_to_user_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}', 'auth_key', $this->string(32)->notNull());
    }

    public function down()
    {
        $this->dropColumn('{{%user}}', 'auth_key');
    }
}