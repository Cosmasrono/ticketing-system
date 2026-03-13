<?php

use yii\db\Migration;

class m251243_700800_add_extension_period_to_contract_renewal extends Migration
{
    public function up()
    {
        $table = 'contract_renewal';
        $column = 'extension_period';
        
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
            if ($this->db->getTableSchema('contract_renewal')->getColumn('extension_period') !== null) {
                $this->dropColumn('contract_renewal', 'extension_period');
            }
        }
    }
}