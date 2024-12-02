<?php

use yii\db\Migration;

/**
 * Class m241202_060338_add_screenshot_to_tickets
 */
class m241202_060338_add_screenshot_to_tickets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add screenshot column
        $this->addColumn('{{%ticket}}', 'screenshot', $this->string(255)->null());
        
        // Optionally, add an index if you'll be searching by screenshot
        $this->createIndex(
            'idx-ticket-screenshot',
            '{{%ticket}}',
            'screenshot'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove the index first
        $this->dropIndex(
            'idx-ticket-screenshot',
            '{{%ticket}}'
        );

        // Then drop the column
        $this->dropColumn('{{%ticket}}', 'screenshot');
    }
}