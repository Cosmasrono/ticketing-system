<?php

use yii\db\Migration;

class m240201_234896_add_name_to_company extends Migration
{
    public function safeUp()
    {
        // Add name column if it doesn't exist
        if (!$this->db->schema->getTableSchema('company')->getColumn('name')) {
            $this->addColumn('company', 'name', $this->string()->notNull()->after('id'));
        }
    }

    public function safeDown()
    {
        // Drop name column if it exists
        if ($this->db->schema->getTableSchema('company')->getColumn('name')) {
            $this->dropColumn('company', 'name');
        }
    }
} 