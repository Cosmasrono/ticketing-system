<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%contract_renewal}}`.
 */
class m250108_095124_create_contract_renewal_table extends Migration
 
    {
        public function safeUp()
        {
            $this->createTable('contract_renewal', [
                'id' => $this->primaryKey(),
                'company_id' => $this->integer()->notNull(),
                'requested_by' => $this->integer()->notNull(),
                'extension_period' => $this->integer()->notNull(),
                'notes' => $this->text(),
                'renewal_status' => "ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'",
                'current_end_date' => $this->date()->notNull(),
                'new_end_date' => $this->date(),
                'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ]);
    
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
                'user',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }
    
        public function safeDown()
        {
            $this->dropForeignKey('fk-contract_renewal-requested_by', 'contract_renewal');
            $this->dropForeignKey('fk-contract_renewal-company_id', 'contract_renewal');
            $this->dropTable('contract_renewal');
        }
    }