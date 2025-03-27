<?php

use yii\db\Migration;

/**
 * Class m250310_140513_m000000_000004_create_contract_renewal_table
 */
class m250310_140513_m000000_000004_create_contract_renewal_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('contract_renewal', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'requested_by' => $this->integer()->notNull(),
            'extension_period' => $this->integer()->notNull(),
            'notes' => $this->text()->defaultValue(null),
            'renewal_status' => "ENUM('pending','approved','rejected') DEFAULT 'pending'",
            'current_end_date' => $this->date()->notNull(),
            'renewed_at' => $this->date()->defaultValue(null),
            'new_end_date' => $this->date()->defaultValue(null),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign keys first
        $this->dropForeignKey('fk-contract_renewal-company_id', 'contract_renewal');
        $this->dropForeignKey('fk-contract_renewal-requested_by', 'contract_renewal');

        // Drop indexes
        $this->dropIndex('idx-contract_renewal-company_id', 'contract_renewal');
        $this->dropIndex('idx-contract_renewal-requested_by', 'contract_renewal');

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
