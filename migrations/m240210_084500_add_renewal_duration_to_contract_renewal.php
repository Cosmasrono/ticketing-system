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
        $this->addColumn('contract_renewal', 'renewal_duration', $this->integer()->notNull()->after('company_id')->comment('Duration in months'));
        
        // Create an index for better performance
        $this->createIndex(
            'idx-contract_renewal-renewal_duration',
            'contract_renewal',
            'renewal_duration'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop the index first
        $this->dropIndex('idx-contract_renewal-renewal_duration', 'contract_renewal');
        
        // Then drop the column
        $this->dropColumn('contract_renewal', 'renewal_duration');
    }
} 