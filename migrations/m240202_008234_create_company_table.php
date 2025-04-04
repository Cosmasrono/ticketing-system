<?php

use yii\db\Migration;

class m240202_008234_create_company_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('company', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'role' => $this->string()->notNull(),
            'company_name' => $this->string()->notNull(),
            'company_email' => $this->string()->notNull()->unique(),
            'company_type' => $this->string()->notNull(),
            'subscription_level' => $this->string()->notNull(),
            'modules' => $this->text(), // For storing JSON or serialized data
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date(),
            'renewed_at' => $this->timestamp(),
        ]);

        // Create index for commonly searched fields
        $this->createIndex(
            'idx-company-email',
            'company',
            'company_email'
        );

        $this->createIndex(
            'idx-company-status',
            'company',
            'status'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('company') !== null) {
            $this->dropIndex('idx-company-email', 'company');
            $this->dropIndex('idx-company-status', 'company');
            $this->dropTable('company');
        }
    }
} 