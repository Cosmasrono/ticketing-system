<?php

use yii\db\Migration;

/**
 * Class m240924_115404_add_assigned_at_to_ticket
 */
class m240924_115404_add_assigned_at_to_ticket extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('ticket', 'assigned_at', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('ticket', 'assigned_at');
 
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240924_115404_add_assigned_at_to_ticket cannot be reverted.\n";

        return false;
    }
    */
}
