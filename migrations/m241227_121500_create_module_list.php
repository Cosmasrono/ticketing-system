<?php

use yii\db\Migration;

/**
 * Class m241227_121500_create_module_list
 */
class m241227_121500_create_module_list extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%module_list}}', [
            'id' => $this->primaryKey(),
            'module_name' => $this->string(255)->notNull(),
            'module_code' => $this->string(50)->notNull()->unique(),
            'description' => $this->text(),
            'price' => $this->decimal(10, 2)->notNull(),
            'category' => $this->string(50)->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Insert default modules
        $this->batchInsert('{{%module_list}}', 
            ['module_name', 'module_code', 'description', 'price', 'category'],
            [
                ['Human Resources', 'HR', 'Employee management, payroll, and HR processes', 100.00, 'CORE'],
                ['Finance', 'FIN', 'Financial management and accounting', 150.00, 'CORE'],
                ['Administration', 'ADMIN', 'System administration and user management', 80.00, 'CORE'],
                ['Members Portal', 'MEMBERS', 'Member management and services', 200.00, 'SACCO'],
                ['Mobile App', 'MOBILE', 'Mobile banking and services', 250.00, 'SACCO'],
                ['Credit Management', 'CREDIT', 'Loan processing and management', 300.00, 'SACCO'],
                ['USSD Services', 'USSD', 'USSD banking services', 180.00, 'SACCO'],
                ['Power BI', 'BI', 'Business Intelligence reporting', 200.00, 'BUSINESS'],
                ['Reports', 'REPORTS', 'Advanced reporting system', 150.00, 'BUSINESS'],
                ['Dashboard', 'DASH', 'Business analytics dashboard', 120.00, 'BUSINESS'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%module_list}}');
    }
} 