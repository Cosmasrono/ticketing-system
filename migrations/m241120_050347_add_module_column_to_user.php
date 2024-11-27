<?php

use yii\db\Migration;

/**
 * Class m241120_050347_add_module_column_to_user
 */
class m241120_050347_add_module_column_to_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('user', 'module', $this->text()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('user', 'module');
    }
}
