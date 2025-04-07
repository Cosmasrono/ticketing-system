<?php

use yii\db\Migration;

class m240318_000000_add_screenshot_url_to_ticket extends Migration
{
    public function up()
    {
        $this->addColumn('ticket', 'screenshot_url', $this->string());
        $this->addColumn('ticket', 'cloudinary_public_id', $this->string());
    }

    public function down()
    {
        $this->dropColumn('ticket', 'screenshot_url');
        $this->dropColumn('ticket', 'cloudinary_public_id');
    }
} 