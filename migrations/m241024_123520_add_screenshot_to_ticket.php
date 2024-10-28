<?php

use yii\db\Migration;

/**
 * Class m241024_123520_add_screenshot_to_ticket
 */
class m241024_123520_add_screenshot_to_ticket extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%ticket}}', 'screenshot', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ticket}}', 'screenshot');
    }
}