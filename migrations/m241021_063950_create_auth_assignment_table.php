<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%auth_assignment}}`.
 */
class m241021_063950_create_auth_assignment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (!$this->db->schema->getTableSchema('auth_assignment', true)) {
            $this->createTable('auth_assignment', [
                'id' => $this->primaryKey(),
                'item_name' => $this->string()->notNull(),
                'user_id' => $this->string()->notNull(),
                'created_at' => $this->integer()->notNull(),
            ]);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%auth_assignment}}');
    }
}
