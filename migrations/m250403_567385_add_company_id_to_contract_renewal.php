<?php

use yii\db\Migration;

class m250403_567385_add_company_id_to_contract_renewal extends Migration
{
    public function up()
    {
        $table = 'contract_renewal';
        $column = 'company_id';
        
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null) {
            echo "Table '$table' does not exist. Skipping migration.\n";
            return true;
        }
        
        if ($tableSchema->getColumn($column) === null) {
            $this->addColumn($table, $column, $this->integer());
        } else {
            echo "Column '$column' already exists. Skipping...\n";
        }
        
        // Add foreign key if needed
        try {
            $this->addForeignKey(
                'fk-contract_renewal-company_id',
                $table,
                $column,
                'company',
                'id',
                'CASCADE'
            );
        } catch (\Exception $e) {
            echo "Foreign key may already exist. Skipping...\n";
        }
    }

    public function down()
    {
        try {
            $this->dropForeignKey('fk-contract_renewal-company_id', 'contract_renewal');
        } catch (\Exception $e) {
            // Foreign key may not exist
        }
        
        if ($this->db->getTableSchema('contract_renewal') !== null) {
            if ($this->db->getTableSchema('contract_renewal')->getColumn('company_id') !== null) {
                $this->dropColumn('contract_renewal', 'company_id');
            }
        }
    }
}