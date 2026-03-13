<?php

use yii\db\Migration;

class m240318_000000_add_screenshot_url_to_ticket extends Migration
{
    public function up()
    {
        $table = 'ticket';
        
        // Skip if table doesn't exist
        if ($this->db->getTableSchema($table) === null) {
            echo "Table '$table' does not exist. Skipping migration.\n";
            return true;
        }
        
        // Check if columns don't already exist before adding
        $tableSchema = $this->db->getTableSchema($table);
        
        if ($tableSchema->getColumn('screenshot_url') === null) {
            $this->addColumn($table, 'screenshot_url', $this->string());
        }
        
        if ($tableSchema->getColumn('cloudinary_public_id') === null) {
            $this->addColumn($table, 'cloudinary_public_id', $this->string());
        }
    }

    public function down()
    {
        $table = 'ticket';
        
        if ($this->db->getTableSchema($table) !== null) {
            $this->dropColumn($table, 'cloudinary_public_id');
            $this->dropColumn($table, 'screenshot_url');
        }
    }
}