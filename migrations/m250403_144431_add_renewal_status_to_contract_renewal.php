<?php

use yii\db\Migration;

/**
 * Class m250403_144431_add_renewal_status_to_contract_renewal
 */
class m250403_144431_add_renewal_status_to_contract_renewal extends Migration
{
     
        public function up()
        {
            // Add the renewal_status column to the contract_renewal table
            $this->addColumn('contract_renewal', 'renewal_status', $this->string()->defaultValue('pending'));
        }
    
        public function down()
        {
            // Drop the renewal_status column
            $this->dropColumn('contract_renewal', 'renewal_status');
        }
    }