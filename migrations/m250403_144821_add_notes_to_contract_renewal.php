<?php

use yii\db\Migration;

/**
 * Class m250403_144821_add_notes_to_contract_renewal
 */
class m250403_144821_add_notes_to_contract_renewal extends Migration
{ 
        public function up()
        {
            // Add the notes column to the contract_renewal table
            $this->addColumn('contract_renewal', 'notes', $this->text()->null());
        }
    
        public function down()
        {
            // Drop the notes column
            $this->dropColumn('contract_renewal', 'notes');
        }
    }