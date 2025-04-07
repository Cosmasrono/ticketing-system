<?php

use yii\db\Migration;

class m240318_004500_add_notes_to_ticket extends Migration
{
    public function up()
    {
        $this->addColumn('ticket', 'notes', $this->text());
    }

    public function down()
    {
        $this->dropColumn('ticket', 'notes');
    }
} 