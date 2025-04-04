<?php

use yii\db\Migration;

/**
 * Class m250403_144200_add_requested_by_to_contract_renewal
 */
class m250403_144200_add_requested_by_to_contract_renewal extends Migration
{
  
    public function up()
    {
        // Add the requested_by column to the contract_renewal table
        $this->addColumn('contract_renewal', 'requested_by', $this->integer());

        // Optionally, add a foreign key if you have a users table
        $this->addForeignKey(
            'fk-contract_renewal-requested_by',
            'contract_renewal',
            'requested_by',
            'users', // Assuming the users table is named 'users'
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        // Remove foreign key if it was added
        $this->dropForeignKey('fk-contract_renewal-requested_by', 'contract_renewal');

        // Drop the requested_by column
        $this->dropColumn('contract_renewal', 'requested_by');
    }
}