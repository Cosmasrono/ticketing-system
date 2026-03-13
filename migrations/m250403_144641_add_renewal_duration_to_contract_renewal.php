<?php

use yii\db\Migration;

/**
 * Class m250403_144641_add_renewal_duration_to_contract_renewal
 */
class m250403_144641_add_renewal_duration_to_contract_renewal extends Migration
{
    public function up()
    {
        $table = 'contract_renewal';
        $column = 'renewal_duration';
        
        // Check if table exists
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null) {
            echo "Table '$table' does not exist. Skipping migration.\n";
            return true;
        }
        
        // Check if column already exists
        if ($tableSchema->getColumn($column) === null) {
            // Add the renewal_duration column to the contract_renewal table
            $this->addColumn($table, $column, $this->string()->defaultValue(''));
        } else {
            echo "Column '$column' already exists in table '$table'. Skipping...\n";
        }
    }

    public function down()
    {
        $table = 'contract_renewal';
        
        if ($this->db->getTableSchema($table) !== null) {
            if ($this->db->getTableSchema($table)->getColumn('renewal_duration') !== null) {
                // Drop the renewal_duration column
                $this->dropColumn($table, 'renewal_duration');
            }
        }
    }
}