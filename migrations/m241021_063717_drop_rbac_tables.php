<?php

use yii\db\Migration;

/**
 * Class m241021_063717_drop_rbac_tables
 */
class m241021_063717_drop_rbac_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241021_063717_drop_rbac_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241021_063717_drop_rbac_tables cannot be reverted.\n";

        return false;
    }
    */
}
