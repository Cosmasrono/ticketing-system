<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%client}}`.
 */
class m250202_172635_add_module_column_to_client_table extends Migration
{
    
    public function safeUp()
    {
        // Add the 'module' column to the 'client' table
        $this->addColumn('client', 'module', $this->string()->after('company_email')); // Adjust position as needed
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop the 'module' column if the migration is reverted
        $this->dropColumn('client', 'module');
    }
}
