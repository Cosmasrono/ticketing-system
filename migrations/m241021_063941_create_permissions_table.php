<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%permissions}}`.
 */
class m241021_063941_create_permissions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%permissions}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'created_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%permissions}}');
    }
}
