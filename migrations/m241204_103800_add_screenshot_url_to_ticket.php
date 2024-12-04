<?php

use yii\db\Migration;

/**
 * Class m241204_103800_add_screenshot_url_to_ticket
 */
class m241204_103800_add_screenshot_url_to_ticket extends Migration
{
    public function safeUp()
    {
        $this->addColumn('ticket', 'screenshotUrl', $this->string()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('ticket', 'screenshotUrl');
    }
}