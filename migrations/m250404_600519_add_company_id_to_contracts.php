<?php

use yii\db\Migration;

class m250404_600519_add_company_id_to_contracts extends Migration
{
    public function safeUp()
    {
        $table = 'contracts';
        
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null) {
            echo "Table '$table' does not exist. Skipping migration.\n";
            return true;
        }
        
        // Check if company_id column exists
        if ($tableSchema->getColumn('company_id') !== null) {
            // Update existing records to use a company_id
            $this->execute("UPDATE contracts SET company_id = (SELECT TOP 1 id FROM company) WHERE company_id IS NULL");
        }

        // Add foreign key with NO ACTION
        try {
            $this->addForeignKey(
                'fk-contracts-company_id',
                $table,
                'company_id',
                'company',
                'id',
                'NO ACTION',
                'NO ACTION'
            );
        } catch (\Exception $e) {
            echo "Foreign key may already exist. Skipping...\n";
        }
    }

    public function safeDown()
    {
        try {
            $this->dropForeignKey('fk-contracts-company_id', 'contracts');
        } catch (\Exception $e) {
            // Foreign key may not exist
        }
        
        if ($this->db->getTableSchema('contracts') !== null) {
            if ($this->db->getTableSchema('contracts')->getColumn('company_id') !== null) {
                $this->dropColumn('contracts', 'company_id');
            }
        }
    }
}