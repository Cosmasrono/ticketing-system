<?php

use yii\db\Migration;


class m240917_114011_update_ticket_status_default extends Migration
{
 
    public function up()
    {
        // Update the existing 'status' column to have a default value of 'pending'
        $this->alterColumn('ticket', 'status', $this->string()->defaultValue('pending'));
    }

    public function down()
    {
        // Revert the 'status' column to its previous default value if necessary
        $this->alterColumn('ticket', 'status', $this->string()->defaultValue('new'));
    }
}

  