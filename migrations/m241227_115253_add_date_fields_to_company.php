<?php

use yii\db\Migration;

/**
 * Class m241227_add_date_fields_to_company
 */
class m241227_115253_add_date_fields_to_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%company}}', 'start_date', $this->date()->notNull());
        $this->addColumn('{{%company}}', 'end_date', $this->date()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%company}}', 'start_date');
        $this->dropColumn('{{%company}}', 'end_date');
    }
}
