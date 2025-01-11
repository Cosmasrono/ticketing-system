<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m240915_130511_add_role_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (!$this->db->getTableSchema('{{%user}}')->getColumn('role')) {
            $this->addColumn('{{%user}}', 'role', $this->string(20)->notNull()->defaultValue('user'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {


        $this->dropColumn('{{%user}}', 'role');

    }
}
