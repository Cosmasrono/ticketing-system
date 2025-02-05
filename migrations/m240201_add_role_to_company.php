<?php

use yii\db\Migration;

class m240201_234560_add_role_to_company extends Migration
{
    public function safeUp()
    {
        // Add role column if it doesn't exist
        if (!$this->db->schema->getTableSchema('company')->getColumn('role')) {
            $this->addColumn('company', 'role', $this->string(50)->defaultValue('developer')->after('company_email'));
        }
    }

    public function safeDown()
    {
        // Drop role column if it exists
        if ($this->db->schema->getTableSchema('company')->getColumn('role')) {
            $this->dropColumn('company', 'role');
        }
    }
} 