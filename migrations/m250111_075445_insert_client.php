<?php

use yii\db\Migration;

/**
 * Class m250111_075445_insert_client
 */
class m250111_075445_insert_client extends Migration
{
    /**
         * {@inheritdoc}
         */
        public function safeUp()
        {
            // First create the client table
            $this->createTable('client', [
                'id' => $this->primaryKey(),
                'company_name' => $this->string()->notNull(),
                'company_email' => $this->string()->notNull(),
                'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ]);

            // Then insert the clients
            $this->batchInsert('client', ['company_name', 'company_email'], [
                ['Ushuru Sacco', 'ushuru@domain.com'],
                ['Mhasibu Sacco', 'mhasibu@domain.com'],
                ['Shirika Sacco', 'shirika@domain.com'],
                ['Mwito Sacco', 'mwito@domain.com'],
                ['Magerezza Sacco', 'magerezza@domain.com'],
                ['Kenya Police Sacco', 'kenyapolice@domain.com'],
                ['Kenya Police Investment', 'policeinvestment@domain.com'],
                ['Tenwek hospital', 'tenwek@domain.com'],
                ['Kiri Consult', 'kiri@domain.com'],
                ['Kewissco Sacco', 'kewissco@domain.com'],
                ['Boresha Sacco', 'boresha@domain.com'],
                ['Afya Sacco', 'afya@domain.com'],
                ['Commodities Fund', 'commodities@domain.com'],
                ['Irrigation Sacco', 'irrigation@domain.com'],
                ['Bandari Sacco', 'bandari@domain.com'],
                ['Nyayo Tea Zones', 'nyayotea@domain.com'],
                ['KEMRI Welcome Trust', 'kemri@domain.com'],
                ['Kapematt Supermarket', 'kapematt@domain.com'],
                ['Kenyatta International Convention Centre', 'kicc@domain.com'],
                ['Africa Academy of Sciences', 'africacademy@domain.com'],
                ['Lubaga Hospital', 'lubaga@domain.com'],
                ['Hlalawati Sacco', 'hlalawati@domain.com'],
                ['Canaan Properties', 'canaan@domain.com']
            ]);
        }
    
        /**
         * {@inheritdoc}
         */
        public function safeDown()
        {
            // In safeDown, we'll just drop the entire table
            $this->dropTable('client');
        }
    }
    