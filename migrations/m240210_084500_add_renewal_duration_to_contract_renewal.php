<?php

use yii\db\Migration;

/**
 * Class m240210_084500_add_renewal_duration_to_contract_renewal
 */
class m240210_084500_add_renewal_duration_to_contract_renewal extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if table exists first
        $table = 'contract_renewals';
        $column = 'renewal_duration';
        
        $tableSchema = $this->db->getTableSchema($table);
        
        // Skip if table doesn't exist
        if ($tableSchema === null) {
            echo "Table '$table' does not exist. Skipping migration.\n";
            return true;
        }
        
        // Only add the column if it doesn't exist
        if ($tableSchema->getColumn($column) === null) {
            $this->addColumn($table, $column, $this->integer()->notNull()->defaultValue(0)->comment('Duration in months'));
        }
        
        // Try to create index (ignore if it already exists)
        try {
            $this->createIndex(
                'idx-contract_renewals-renewal_duration',
                $table,
                'renewal_duration'
            );
        } catch (\Exception $e) {
            echo "Index may already exist, skipping...\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $table = 'contract_renewals';
        
        // Drop the index first
        try {
            $this->dropIndex('idx-contract_renewals-renewal_duration', $table);
        } catch (\Exception $e) {
            // Index may not exist
        }
        
        // Then drop the column
        $this->dropColumn($table, 'renewal_duration');
    }
}