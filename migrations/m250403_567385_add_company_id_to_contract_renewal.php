<?php

use yii\db\Migration;

class m250403_567385_add_company_id_to_contract_renewal extends Migration
{
    public function up()
    {
        $this->addColumn('contract_renewal', 'company_id', $this->integer());
        
        // Add foreign key if needed
        $this->addForeignKey(
            'fk-contract_renewal-company_id',
            'contract_renewal',
            'company_id',
            'company',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        // Remove foreign key if added
        $this->dropForeignKey('fk-contract_renewal-company_id', 'contract_renewal');
        
        $this->dropColumn('contract_renewal', 'company_id');
    }
} 