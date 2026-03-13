<?php

use yii\db\Migration;

class m251114_094802_alter_ticket_renewal_status_to_string extends Migration
{
  
    public function up()
    {
        $this->alterColumn('ticket', 'renewal_status', $this->string(50)->defaultValue('pending'));
    }

    public function down()
    {
        $this->alterColumn('ticket', 'renewal_status', $this->integer()->defaultValue(0));
    }
}