<?php

use yii\db\Migration;

class m250453_458946_add_current_end_date_to_contract_renewal extends Migration
{
    public function up()
    {
        $this->addColumn('contract_renewal', 'current_end_date', $this->dateTime());
    }

    public function down()
    {
        $this->dropColumn('contract_renewal', 'current_end_date');
    }
} 