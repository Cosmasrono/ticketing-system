<?php

use yii\db\Migration;

class m240915_234890_create_contract_renewals_table extends Migration
{
    public function up()
    {
        $this->createTable('contract_renewals', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'requested_by' => $this->integer()->notNull(),
            'extension_period' => $this->integer()->notNull(), // in months
            'notes' => $this->text(),
            'status' => $this->string()->defaultValue('pending'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'approved_at' => $this->timestamp()->null(),
            'approved_by' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk-contract_renewals-company_id',
            'contract_renewals',
            'company_id',
            'company',
            'id'
        );

        $this->addForeignKey(
            'fk-contract_renewals-requested_by',
            'contract_renewals',
            'requested_by',
            'user',
            'id'
        );
    }

    public function down()
    {
        $this->dropTable('contract_renewals');
    }
} 