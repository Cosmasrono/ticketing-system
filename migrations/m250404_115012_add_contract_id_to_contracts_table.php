<?php

use yii\db\Migration;

class m250404_115012_add_contract_id_to_contracts_table extends Migration
{
    public function safeUp()
    {
        $table = 'contracts';
        $column = 'contract_id';
        
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null) {
            echo "Table '$table' does not exist. Skipping migration.\n";
            return true;
        }
        
        if ($tableSchema->getColumn($column) === null) {
            // Remove the after() - not supported in SQL Server
            $this->addColumn($table, $column, $this->integer()->notNull()->defaultValue(0));
        } else {
            echo "Column '$column' already exists. Skipping...\n";
        }
    }

    public function safeDown()
    {
        try {
            $this->dropForeignKey('fk-contracts-contract_id', 'contracts');
        } catch (\Exception $e) {
            // Foreign key may not exist
        }

        if ($this->db->getTableSchema('contracts') !== null) {
            if ($this->db->getTableSchema('contracts')->getColumn('contract_id') !== null) {
                $this->dropColumn('contracts', 'contract_id');
            }
        }
    }
}