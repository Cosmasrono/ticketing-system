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
            // Check if the table exists
            if (\Yii::$app->db->schema->getTableSchema('client', true)) {
                // Check if the columns exist, and add them if they don't
                $tableSchema = \Yii::$app->db->schema->getTableSchema('client');
                
                if (!isset($tableSchema->columns['name'])) {
                    $this->addColumn('client', 'name', $this->string()->notNull());
                }
                
                if (!isset($tableSchema->columns['company_name'])) {
                    $this->addColumn('client', 'company_name', $this->string()->notNull());
                }
                
                if (!isset($tableSchema->columns['company_email'])) {
                    $this->addColumn('client', 'company_email', $this->string()->notNull());
                }

                $currentTimestamp = date('Y-m-d H:i:s');

                // Now insert the data with timestamps and names
                $this->batchInsert('client', 
                    ['name', 'company_name', 'company_email', 'created_at', 'updated_at'], 
                    [
                        ['John Doe', 'Ushuru Sacco', 'ushuru@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Jane Smith', 'Mhasibu Sacco', 'mhasibu@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Mike Johnson', 'Shirika Sacco', 'shirika@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Sarah Wilson', 'Mwito Sacco', 'mwito@domain.com', $currentTimestamp, $currentTimestamp],
                        ['David Brown', 'Magerezza Sacco', 'magerezza@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Emily Davis', 'Kenya Police Sacco', 'kenyapolice@domain.com', $currentTimestamp, $currentTimestamp],
                        ['James Miller', 'Kenya Police Investment', 'policeinvestment@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Lisa Anderson', 'Tenwek hospital', 'tenwek@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Robert Taylor', 'Kiri Consult', 'kiri@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Mary Thomas', 'Kewissco Sacco', 'kewissco@domain.com', $currentTimestamp, $currentTimestamp],
                        ['William Moore', 'Boresha Sacco', 'boresha@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Patricia White', 'Afya Sacco', 'afya@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Michael Lee', 'Commodities Fund', 'commodities@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Jennifer Clark', 'Irrigation Sacco', 'irrigation@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Charles Hall', 'Bandari Sacco', 'bandari@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Margaret Young', 'Nyayo Tea Zones', 'nyayotea@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Daniel King', 'KEMRI Welcome Trust', 'kemri@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Susan Wright', 'Kapematt Supermarket', 'kapematt@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Joseph Scott', 'Kenyatta International Convention Centre', 'kicc@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Nancy Green', 'Africa Academy of Sciences', 'africacademy@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Christopher Baker', 'Lubaga Hospital', 'lubaga@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Amanda Adams', 'Hlalawati Sacco', 'hlalawati@domain.com', $currentTimestamp, $currentTimestamp],
                        ['Kevin Nelson', 'Canaan Properties', 'canaan@domain.com', $currentTimestamp, $currentTimestamp],
                    ]
                );
            } else {
                echo "Table 'client' does not exist. Migration skipped.\n";
            }
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
    