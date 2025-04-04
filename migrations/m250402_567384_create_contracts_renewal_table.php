<?php

use yii\db\Migration;

class m250402_567384_create_contracts_renewal_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('contract_renewal', [
            'id' => $this->primaryKey(),
            'contract_id' => $this->integer()->notNull(),
            'renewed_at' => $this->dateTime(),
            'renewal_date' => $this->date(),
            'renewal_amount' => $this->decimal(10, 2),
            'status' => $this->string(20)->defaultValue('pending'),
            'remarks' => $this->text(),
            'created_by' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // Add indexes
        $this->createIndex(
            'idx-contract_renewal-contract_id',
            'contract_renewal',
            'contract_id'
        );

        $this->createIndex(
            'idx-contract_renewal-renewed_at',
            'contract_renewal',
            'renewed_at'
        );

        // Add foreign key
        $this->addForeignKey(
            'fk-contract_renewal-contract_id',
            'contract_renewal',
            'contract_id',
            'contracts',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey('fk-contract_renewal-contract_id', 'contract_renewal');

        // Drop indexes
        $this->dropIndex('idx-contract_renewal-contract_id', 'contract_renewal');
        $this->dropIndex('idx-contract_renewal-renewed_at', 'contract_renewal');

        // Drop table
        $this->dropTable('contract_renewal');
    }
} 