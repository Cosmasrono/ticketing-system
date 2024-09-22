<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%ticket}}`.
 */
class m240919_171159_add_assigned_to_column_to_ticket_table extends Migration
{public function up()
    {
        $this->addColumn('{{%ticket}}', 'assigned_to', $this->integer());
    }
    
    public function down()
    {
        $this->dropColumn('{{%ticket}}', 'assigned_to');
    }
}    
