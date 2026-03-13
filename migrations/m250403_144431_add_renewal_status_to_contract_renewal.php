<?php

use yii\db\Migration;

class m250403_144431_add_renewal_status_to_contract_renewal extends Migration
{
    public function up()
    {
        $table = 'contract_renewal';
        $column = 'renewal_status';
        
        // Check if table exists
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null) {
            echo "Table '$table' does not exist. Skipping migration.\n";
            return true;
        }
        
        // Check if column already exists
        if ($tableSchema->getColumn($column) === null) {
            $this->addColumn($table, $column, $this->string()->defaultValue('pending'));
        } else {
            echo "Column '$column' already exists in table '$table'. Skipping...\n";
        }
    }

    public function down()
    {
        $table = 'contract_renewal';
        
        if ($this->db->getTableSchema($table) !== null) {
            if ($this->db->getTableSchema($table)->getColumn('renewal_status') !== null) {
                $this->dropColumn($table, 'renewal_status');
            }
        }
    }
}