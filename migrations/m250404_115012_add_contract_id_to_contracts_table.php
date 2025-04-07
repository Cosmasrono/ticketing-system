<?php

use yii\db\Migration;

/**
 * Class m250404_115012_add_contract_id_to_contracts_table
 */
class m250404_115012_add_contract_id_to_contracts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add the contract_id column to the contracts table
        $this->addColumn('contracts', 'contract_id', $this->integer()->notNull()->after('id'));

        // Replace 'actual_contracts_table' with the correct table name
       
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove the foreign key constraint if it was added
        $this->dropForeignKey('fk-contracts-contract_id', 'contracts');

        // Drop the contract_id column
        $this->dropColumn('contracts', 'contract_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250404_115012_add_contract_id_to_contracts_table cannot be reverted.\n";

        return false;
    }
    */
}
