<?php

use yii\db\Migration;

/**
 * Class m240920_110901_add_status_to_ticket
 */
class m240920_110901_add_status_to_ticket extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('ticket', 'status', $this->string()->notNull()->defaultValue('pending')); // Adjust as needed
    }
}