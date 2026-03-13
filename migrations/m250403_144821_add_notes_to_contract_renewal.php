<?php

use yii\db\Migration;

/**
 * Class m250403_144821_add_notes_to_contract_renewal
 */
class m250403_144821_add_notes_to_contract_renewal extends Migration
{ 
    public function up()
    {
        $table = 'contract_renewal';
        $column = 'notes';
        
        // ↓ THIS CHECKS IF TABLE EXISTS
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null) {
            echo "Table '$table' does not exist. Skipping migration.\n";
            return true;
        }
        
        // ↓ THIS CHECKS IF COLUMN ALREADY EXISTS
        if ($tableSchema->getColumn($column) === null) {
            // Add the notes column to the contract_renewal table
            $this->addColumn($table, $column, $this->text()->null());
        } else {
            echo "Column '$column' already exists in table '$table'. Skipping...\n";
        }
    }

    public function down()
    {
        // ↓ ALSO CHECK BEFORE DROPPING
        if ($this->db->getTableSchema('contract_renewal') !== null) {
            if ($this->db->getTableSchema('contract_renewal')->getColumn('notes') !== null) {
                // Drop the notes column
                $this->dropColumn('contract_renewal', 'notes');
            }
        }
    }
}