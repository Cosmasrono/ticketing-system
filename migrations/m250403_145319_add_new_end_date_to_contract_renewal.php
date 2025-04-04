<?php

use yii\db\Migration;

/**
 * Class m250403_145319_add_new_end_date_to_contract_renewal
 */
class m250403_145319_add_new_end_date_to_contract_renewal extends Migration
{
      public function up()
        {
            $this->addColumn('contract_renewal', 'new_end_date', $this->date());
        }
    
        public function down()
        {
            $this->dropColumn('contract_renewal', 'new_end_date');
        }
    }