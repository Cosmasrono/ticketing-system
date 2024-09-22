<?php

use yii\db\Migration;

/**
 * Class m240917_092632_add_developer_id_to_ticket
 */
class m240917_092632_add_developer_id_to_ticket extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
{
    $this->addColumn('{{%ticket}}', 'developer_id', $this->integer()->after('id'));
    $this->addForeignKey('fk-ticket-developer_id', '{{%ticket}}', 'developer_id', '{{%developer}}', 'id', 'SET NULL');
}

public function down()
{
    $this->dropForeignKey('fk-ticket-developer_id', '{{%ticket}}');
    $this->dropColumn('{{%ticket}}', 'developer_id');
}
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240917_092632_add_developer_id_to_ticket cannot be reverted.\n";

        return false;
    }
    */
}
