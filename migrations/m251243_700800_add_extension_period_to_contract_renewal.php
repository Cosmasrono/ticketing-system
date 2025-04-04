<?php

use yii\db\Migration;

class m251243_700800_add_extension_period_to_contract_renewal extends Migration
{
    public function up()
    {
        $this->addColumn('contract_renewal', 'extension_period', $this->dateTime());
    }

    public function down()
    {
        $this->dropColumn('contract_renewal', 'extension_period');
    }
} 