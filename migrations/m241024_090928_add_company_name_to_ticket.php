<?php

use yii\db\Migration;

/**
 * Class m241024_090928_add_company_name_to_ticket
 */
class m241024_090928_add_company_name_to_ticket extends Migration
{
    
     public function safeUp()
    {
        $this->addColumn('{{%ticket}}', 'company_name', $this->string()->after('id')->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ticket}}', 'company_name');
    }
}