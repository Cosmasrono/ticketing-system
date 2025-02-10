<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%client}}`.
 */
class m250202_172635_add_module_column_to_client_table extends Migration
{
    public function up()
    {
        // Check if the column already exists
        if (!$this->db->schema->getTableSchema('client')->getColumn('module')) {
            $this->addColumn('client', 'module', $this->string()->after('company_email'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // Remove the column if it exists
        if ($this->db->schema->getTableSchema('client')->getColumn('module')) {
            $this->dropColumn('client', 'module');
        }
    }
}
