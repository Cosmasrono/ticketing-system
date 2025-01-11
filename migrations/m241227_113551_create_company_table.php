<?php

use yii\db\Migration;

/**
 * Class m240127_create_company_table
 */
class m241227_113551_create_company_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%company}}', [
            'id' => $this->primaryKey(),
            'company_name' => $this->string(255)->notNull(),
            'company_email' => $this->string(255)->notNull(),
            'company_type' => $this->string(50)->notNull(),
            'subscription_level' => $this->string(50)->notNull(),
            'modules' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'status' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $tableOptions);

        // Add unique indexes
        $this->createIndex(
            '{{%idx-company-name}}',
            '{{%company}}',
            'company_name',
            true
        );

        $this->createIndex(
            '{{%idx-company-email}}',
            '{{%company}}',
            'company_email',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%company}}');
    }
}
