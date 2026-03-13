<?php

use yii\db\Migration;

class m250453_458946_add_current_end_date_to_contract_renewal extends Migration
{
    public function up()
    {
        $table = 'contract_renewal';
        $column = 'current_end_date';
        
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null) {
            echo "Table '$table' does not exist. Skipping migration.\n";
            return true;
        }
        
        if ($tableSchema->getColumn($column) === null) {
            $this->addColumn($table, $column, $this->dateTime());
        } else {
            echo "Column '$column' already exists. Skipping...\n";
        }
    }

    public function down()
    {
        if ($this->db->getTableSchema('contract_renewal') !== null) {
            if ($this->db->getTableSchema('contract_renewal')->getColumn('current_end_date') !== null) {
                $this->dropColumn('contract_renewal', 'current_end_date');
            }
        }
    }
}