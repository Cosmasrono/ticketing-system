<?php

use yii\db\Migration;

class m240405_000000_update_ticket_columns extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('ticket', 'screenshot_url', $this->text());
        $this->alterColumn('ticket', 'voice_note_url', $this->text());
    }

    public function safeDown()
    {
        $this->alterColumn('ticket', 'screenshot_url', $this->string(255));
        $this->alterColumn('ticket', 'voice_note_url', $this->string(255));
    }
} 