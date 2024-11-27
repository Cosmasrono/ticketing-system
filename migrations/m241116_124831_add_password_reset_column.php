<?php

use yii\db\Migration;

/**
 * Class m241116_124831_add_password_reset_column
 */
class m241116_124831_add_password_reset_column extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'is_password_reset', $this->boolean()->defaultValue(false));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'is_password_reset');
    }
}
