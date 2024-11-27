<?php

use yii\db\Migration;

/**
 * Class m241120_054920_add_selected_modules_to_user
 */
class m241120_054920_add_selected_modules_to_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('user', 'selectedModules', $this->text()->null()->after('role'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'selectedModules');
    }
}
