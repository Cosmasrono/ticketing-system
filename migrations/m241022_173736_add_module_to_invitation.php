<?php

use yii\db\Migration;

/**
 * Class m241022_173736_add_module_to_invitation
 */
class m241022_173736_add_module_to_invitation extends Migration
{
 
        /**
         * {@inheritdoc}
         */
        public function safeUp()
        {
            $this->addColumn('{{%invitation}}', 'module', $this->string()->notNull()->after('role'));
        }
    
        /**
         * {@inheritdoc}
         */
        public function safeDown()
        {
            $this->dropColumn('{{%invitation}}', 'module');
        }
    }