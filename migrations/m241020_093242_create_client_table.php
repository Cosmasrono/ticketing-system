<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%client}}`.
 */
class m241020_093242_create_client_table extends Migration
{
 
        public function up()
        {
            $this->createTable('{{%client}}', [
                'id' => $this->primaryKey(),
                'company_email' => $this->string()->notNull()->unique(),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
            ]);
    
            // Create an index on company_email for faster lookups
            $this->createIndex('idx-client-company_email', '{{%client}}', 'company_email');
            
        }
    
        public function down()
        {
            // Drop the index
            $this->dropIndex('idx-client-company_email', '{{%client}}');
    
            // Drop the table
            $this->dropTable('{{%client}}');
        }
    }