<?php

use yii\db\Migration;

class m230101_000000_create_invitation_table extends Migration
{
    public function up()
    {
        $this->createTable('invitation', [
            'id' => $this->primaryKey(),
            'company_email' => $this->string()->notNull(),
            'role' => $this->string()->notNull(),
            'token' => $this->string(32)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-invitation-token', 'invitation', 'token', true);
    }

    public function down()
    {
        $this->dropTable('invitation');
    }
}
