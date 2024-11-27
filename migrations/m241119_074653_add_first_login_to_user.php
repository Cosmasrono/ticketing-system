<?php

use yii\db\Migration;

/**
 * Class m241119_074653_add_first_login_to_user
 */
class m241119_074653_add_first_login_to_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('user', 'first_login', $this->boolean()->defaultValue(1)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'first_login');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241119_074653_add_first_login_to_user cannot be reverted.\n";

        return false;
    }
    */
}
