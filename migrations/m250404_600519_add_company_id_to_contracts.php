<?php

use yii\db\Migration;

class m250404_600519_add_company_id_to_contracts extends Migration
{
    public function safeUp()
    {
        // Update existing records to use a company_id
        $this->execute("UPDATE contracts SET company_id = (SELECT TOP 1 id FROM company)");

        // Add foreign key with NO ACTION
        $this->addForeignKey(
            'fk-contracts-company_id',
            'contracts',
            'company_id',
            'company',
            'id',
            'NO ACTION',
            'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-contracts-company_id', 'contracts');
        $this->dropColumn('contracts', 'company_id');
    }
} 