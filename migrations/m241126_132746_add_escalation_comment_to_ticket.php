<?php

use yii\db\Migration;

/**
 * Class m241126_132746_add_escalation_comment_to_ticket
 */
class m241126_132746_add_escalation_comment_to_ticket extends Migration
{ 
    public function safeUp()
    {
        $this->addColumn('ticket', 'escalation_comment', $this->text()->null()->comment('Comment provided when escalating the ticket'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('ticket', 'escalation_comment');
    }
}