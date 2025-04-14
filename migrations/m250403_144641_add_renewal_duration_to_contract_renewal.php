<?php

use yii\db\Migration;

/**
 * Class m250403_144641_add_renewal_duration_to_contract_renewal
 */
class m250403_144641_add_renewal_duration_to_contract_renewal extends Migration
{
 
        public function up()
        {
            // Add the renewal_duration column to the contract_renewal table
            $this->addColumn('contract_renewal', 'renewal_duration', $this->string()->defaultValue('')); // Adjust type as needed
        }
    
        public function down()
        {
            // Drop the renewal_duration column
            $this->dropColumn('contract_renewal', 'renewal_duration');
        }
    }