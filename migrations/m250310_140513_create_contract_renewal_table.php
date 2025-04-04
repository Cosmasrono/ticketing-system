<?php

use yii\db\Migration;

/**
 * Class m250310_140513_m000000_000004_create_contract_renewal_table
 */
class m250310_140513_create_contract_renewal_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if table exists first
        if ($this->db->getTableSchema('contract_renewal') !== null) {
            // Either skip the migration
            return true;
            
            // OR if you want to drop and recreate (WARNING: this will delete existing data)
            // $this->dropTable('contract_renewal');
        }

        $this->createTable('contract_renewal', [
            'id' => $this->primaryKey(),
            'contract_id' => $this->integer()->notNull(),
            'company_id' => $this->integer()->notNull(),
            'requested_by' => $this->integer()->notNull(),
            'extension_period' => $this->integer()->notNull(),
            'notes' => $this->text()->defaultValue(null),
            'renewal_status' => $this->string(20)->defaultValue('pending'),
            'current_end_date' => $this->date()->notNull(),
            'renewed_at' => $this->date()->defaultValue(null),
            'new_end_date' => $this->date()->defaultValue(null),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')
        ]);

        // Add CHECK constraint for renewal_status
        $this->execute("ALTER TABLE contract_renewal ADD CONSTRAINT CK_contract_renewal_status CHECK (renewal_status IN ('pending', 'approved', 'rejected'))");

        // Add indexes
        $this->createIndex(
            'idx-contract_renewal-company_id',
            'contract_renewal',
            'company_id'
        );

        $this->createIndex(
            'idx-contract_renewal-requested_by',
            'contract_renewal',
            'requested_by'
        );

        // Add new index for contract_id
        $this->createIndex(
            'idx-contract_renewal-contract_id',
            'contract_renewal',
            'contract_id'
        );

        // Add foreign keys
        $this->addForeignKey(
            'fk-contract_renewal-company_id',
            'contract_renewal',
            'company_id',
            'company',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-contract_renewal-requested_by',
            'contract_renewal',
            'requested_by',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add foreign key for contract_id
        $this->addForeignKey(
            'fk-contract_renewal-contract_id',
            'contract_renewal',
            'contract_id',
            'contracts',  // Changed from 'contract' to 'contracts'
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign keys first (these have already been dropped successfully)
        // $this->dropForeignKey('fk-contract_renewal-contract_id', 'contract_renewal');
        // $this->dropForeignKey('fk-contract_renewal-company_id', 'contract_renewal');
        // $this->dropForeignKey('fk-contract_renewal-requested_by', 'contract_renewal');

        // Check if indexes exist before dropping
        $indexExists = $this->db->createCommand("
            SELECT COUNT(*) 
            FROM sys.indexes 
            WHERE name = 'idx-contract_renewal-company_id' 
            AND object_id = OBJECT_ID('contract_renewal')
        ")->queryScalar();

        if ($indexExists > 0) {
            $this->dropIndex('idx-contract_renewal-company_id', 'contract_renewal');
        }

        $indexExists = $this->db->createCommand("
            SELECT COUNT(*) 
            FROM sys.indexes 
            WHERE name = 'idx-contract_renewal-requested_by' 
            AND object_id = OBJECT_ID('contract_renewal')
        ")->queryScalar();

        if ($indexExists > 0) {
            $this->dropIndex('idx-contract_renewal-requested_by', 'contract_renewal');
        }

        // Drop the table
        $this->dropTable('contract_renewal');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250310_140513_m000000_000004_create_contract_renewal_table cannot be reverted.\n";

        return false;
    }
    */
}
