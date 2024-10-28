<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%company_module_issue}}`.
 */
class m241022_170716_create_company_module_issue_table extends Migration
{ 
  /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%company_module_issue}}', [
            'id' => $this->primaryKey(),
            'company_email' => $this->string(255)->notNull(),
            'module' => $this->string(255)->notNull(),
            'issue' => $this->string(255)->notNull(),
        ]);

        // Add unique constraint
        $this->createIndex(
            'idx-company_module_issue-unique',
            '{{%company_module_issue}}',
            ['company_email', 'module', 'issue'],
            true
        );

        // Add foreign key to link with the user table
        $this->addForeignKey(
            'fk-company_module_issue-company_email',
            '{{%company_module_issue}}',
            'company_email',
            '{{%user}}',
            'company_email',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey('fk-company_module_issue-company_email', '{{%company_module_issue}}');

        // Drop the table
        $this->dropTable('{{%company_module_issue}}');
    }
}