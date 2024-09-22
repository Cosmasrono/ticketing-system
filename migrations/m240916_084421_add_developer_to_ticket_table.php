<?php

use yii\db\Migration;

class m240916_084421_add_developer_to_ticket_table extends Migration
{
    public function up()
    {
        $this->addColumn('ticket', 'assigned_to', $this->integer()->after('status'));
        $this->addForeignKey('fk-ticket-assigned_to', 'ticket', 'assigned_to', 'developer', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk-ticket-assigned_to', 'ticket');
        $this->dropColumn('ticket', 'assigned_to');
    }
}
