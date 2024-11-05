<?php

use yii\db\Migration;

/**
 * Class m241104_123456_add_expires_at_column
 */
class m241104_123456_add_expires_at_column extends Migration
{
 
        /**
         * {@inheritdoc}
         */
        public function safeUp()
        {
            $this->addColumn('{{%invitation}}', 'expires_at', $this->timestamp()->null()->after('module'));
        }
    
        /**
         * {@inheritdoc}
         */
        public function safeDown()
        {
            $this->dropColumn('{{%invitation}}', 'expires_at');
        }
    }