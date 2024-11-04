<?php

use yii\db\Migration;

/**
 * Class m241104_094543_add_company_name_to_invitation
 */
class m241104_094543_add_company_name_to_invitation extends Migration
{
 
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('invitation', 'company_name', $this->string()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('invitation', 'company_name');
    }
}