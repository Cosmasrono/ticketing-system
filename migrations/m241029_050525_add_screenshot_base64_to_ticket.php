<?php

use yii\db\Migration;

/**
 * Class m241029_050525_add_screenshot_base64_to_ticket
 */
class m241029_050525_add_screenshot_base64_to_ticket extends Migration
{
    
        public function safeUp()
        {
            $this->addColumn('ticket', 'screenshot_base64', $this->text()->null());
        }
    
        public function safeDown()
        {
            $this->dropColumn('ticket', 'screenshot_base64');
        }
    }