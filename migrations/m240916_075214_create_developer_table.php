<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%developer}}`.
 */
class m240916_075214_create_developer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if (!$this->db->schema->getTableSchema('developer', true)) {
            $this->createTable('developer', [
                'id' => $this->primaryKey(),
                'name' => $this->string()->notNull(),
            ]);
        }
    }
    

    /**
     * {@inheritdoc}
     */
    public function down()  
    {
        $this->dropTable('developer');
    }
}