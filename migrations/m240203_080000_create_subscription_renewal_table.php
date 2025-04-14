<?php

use yii\db\Migration;

class m240203_080000_create_subscription_renewal_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('subscription_renewal', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'renewal_duration' => $this->integer()->notNull()->comment('Duration in months'),
            'requested_by' => $this->integer()->notNull(),
            'extension_period' => $this->integer(),
            'notes' => $this->text(),
            'renewal_status' => $this->string()->notNull()->defaultValue('pending'),
            'current_end_date' => $this->date()->notNull(),
            'renewed_at' => $this->timestamp(),
            'new_end_date' => $this->date(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Create indexes
        $this->createIndex(
            'idx-subscription_renewal-company_id',
            'subscription_renewal',
            'company_id'
        );

        $this->createIndex(
            'idx-subscription_renewal-requested_by',
            'subscription_renewal',
            'requested_by'
        );

        // Add foreign key for company_id
        $this->addForeignKey(
            'fk-subscription_renewal-company_id',
            'subscription_renewal',
            'company_id',
            'company',
            'id',
            'CASCADE'
        );

        // Add foreign key for requested_by
        $this->addForeignKey(
            'fk-subscription_renewal-requested_by',
            'subscription_renewal',
            'requested_by',
            'users',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('subscription_renewal') !== null) {
            $this->dropForeignKey('fk-subscription_renewal-company_id', 'subscription_renewal');
            $this->dropForeignKey('fk-subscription_renewal-requested_by', 'subscription_renewal');
            $this->dropIndex('idx-subscription_renewal-company_id', 'subscription_renewal');
            $this->dropIndex('idx-subscription_renewal-requested_by', 'subscription_renewal');
            $this->dropTable('subscription_renewal');
        }
    }
} 