<?php

use yii\db\Migration;

/**
 * Class m250310_140813_add_renewal_duration_to_contract_renewal
 */
class m250310_140813_add_renewal_duration_to_contract_renewal extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('contract_renewal', 'renewal_duration', $this->integer()->notNull()->comment('Duration in months')->after('company_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('contract_renewal', 'renewal_duration');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250310_140813_add_renewal_duration_to_contract_renewal cannot be reverted.\n";

        return false;
    }
    */
}
