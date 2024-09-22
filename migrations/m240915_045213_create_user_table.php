<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m240915_045213_create_user_table extends Migration
{
 
    public function up()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'company_email' => $this->string()->notNull()->unique(),
            'company_name' => $this->string()->notNull(),
            'password_hash' => $this->string()->notNull(),
            'auth_key' => $this->string(32)->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('user');
    }
}