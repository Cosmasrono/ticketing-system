<?php

use yii\db\Migration;

class m250403_145319_add_new_end_date_to_contract_renewal extends Migration
{
    public function up()
    {
        $table = 'contract_renewal';
        $column = 'new_end_date';
        
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null) {
            echo "Table '$table' does not exist. Skipping migration.\n";
            return true;
        }
        
        if ($tableSchema->getColumn($column) === null) {
            $this->addColumn($table, $column, $this->date());
        } else {
            echo "Column '$column' already exists. Skipping...\n";
        }
    }

    public function down()
    {
        if ($this->db->getTableSchema('contract_renewal') !== null) {
            if ($this->db->getTableSchema('contract_renewal')->getColumn('new_end_date') !== null) {
                $this->dropColumn('contract_renewal', 'new_end_date');
            }
        }
    }
}