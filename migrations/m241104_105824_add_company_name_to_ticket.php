<?php

use yii\db\Migration;

/**
 * Class m241104_105824_add_company_name_to_ticket
 */
class m241104_105824_add_company_name_to_ticket extends Migration
{
    
        /**
         * {@inheritdoc}
         */
        public function safeUp()
        {
            $this->addColumn('ticket', 'company_name', $this->string()->defaultValue(null)->after('company_email'));
    
            // Populate existing tickets with company names from users table
            $this->execute("
                UPDATE ticket t
                JOIN user u ON t.created_by = u.id
                SET t.company_name = u.company_name
                WHERE t.company_name IS NULL
            ");
        }
    
        /**
         * {@inheritdoc}
         */
        public function safeDown()
        {
            $this->dropColumn('ticket', 'company_name');
        }
    }