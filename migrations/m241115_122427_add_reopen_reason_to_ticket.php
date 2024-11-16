<?php

use yii\db\Migration;

/**
 * Class m241115_122427_add_reopen_reason_to_ticket
 */
class m241115_122427_add_reopen_reason_to_ticket extends Migration
{
    public function safeUp()
    {
        $this->addColumn('ticket', 'reopen_reason', $this->text()->null()->after('status'));
    }

    public function safeDown()
    {
        $this->dropColumn('ticket', 'reopen_reason');
    }
}