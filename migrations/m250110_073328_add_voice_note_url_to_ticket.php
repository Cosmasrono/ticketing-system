<?php

use yii\db\Migration;

/**
 * Class m250110_073328_add_voice_note_url_to_ticket
 */
class m250110_073328_add_voice_note_url_to_ticket extends Migration
{ 
        /**
         * {@inheritdoc}
         */
        public function up()
        {
            // Add the voice_note_url column to the ticket table
            $this->addColumn('ticket', 'voice_note_url', $this->string()->null());
        }
    
        /**
         * {@inheritdoc}
         */
        public function down()
        {
            // Remove the voice_note_url column from the ticket table
            $this->dropColumn('ticket', 'voice_note_url');
        }
    }