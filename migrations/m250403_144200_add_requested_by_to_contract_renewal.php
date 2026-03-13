<?php

use yii\db\Migration;

/**
 * Class m250403_144200_add_requested_by_to_contract_renewal
 */
class m250403_144200_add_requested_by_to_contract_renewal extends Migration
{
    public function up()
    {
        $table = 'contract_renewal';
        $column = 'requested_by';
        
        // Check if table exists
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null) {
            echo "Table '$table' does not exist. Skipping migration.\n";
            return true;
        }
        
        // Check if column already exists
        if ($tableSchema->getColumn($column) === null) {
            // Add the requested_by column to the contract_renewal table
            $this->addColumn($table, $column, $this->integer());
        } else {
            echo "Column '$column' already exists in table '$table'. Skipping column creation...\n";
        }

        // Optionally, add a foreign key if you have a users table
        try {
            $this->addForeignKey(
                'fk-contract_renewal-requested_by',
                $table,
                $column,
                'users',
                'id',
                'CASCADE'
            );
        } catch (\Exception $e) {
            echo "Foreign key 'fk-contract_renewal-requested_by' may already exist. Skipping...\n";
        }
    }

    public function down()
    {
        $table = 'contract_renewal';
        
        if ($this->db->getTableSchema($table) === null) {
            return true;
        }
        
        // Remove foreign key if it was added
        try {
            $this->dropForeignKey('fk-contract_renewal-requested_by', $table);
        } catch (\Exception $e) {
            // Foreign key may not exist
        }

        // Drop the requested_by column
        if ($this->db->getTableSchema($table)->getColumn('requested_by') !== null) {
            $this->dropColumn($table, 'requested_by');
        }
    }
}