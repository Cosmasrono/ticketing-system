<?php

use yii\db\Migration;

class m250124_080532_add_renewed_at_column_to_contract_renewal extends Migration
{
    public function up()
    {
        $this->addColumn('contract_renewal', 'renewed_at', $this->date()->after('current_end_date'));
    }

    public function down()
    {
        $this->dropColumn('contract_renewal', 'renewed_at');
    }
} 