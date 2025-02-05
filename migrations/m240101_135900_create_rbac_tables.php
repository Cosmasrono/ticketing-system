<?php
use yii\db\Migration;

class m240101_135900_create_rbac_tables extends Migration
{
    public function up()
    {
        // Drop existing tables if they exist
        try {
            $this->dropTable('auth_assignment');
        } catch(\Exception $e) {}
        
        try {
            $this->dropTable('auth_item_child');
        } catch(\Exception $e) {}
        
        try {
            $this->dropTable('auth_item');
        } catch(\Exception $e) {}
        
        try {
            $this->dropTable('auth_rule');
        } catch(\Exception $e) {}

        // Now create the tables fresh
        $this->createTable('auth_rule', [
            'name' => $this->string(64)->notNull(),
            'data' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY (name)',
        ]);

        $this->createTable('auth_item', [
            'name' => $this->string(64)->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY (name)',
        ]);
        $this->addForeignKey('fk_auth_item_rule_name', 'auth_item', 'rule_name', 'auth_rule', 'name', 'SET NULL', 'NO ACTION');

        $this->createTable('auth_item_child', [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
            'PRIMARY KEY (parent, child)',
        ]);
        $this->addForeignKey('fk_auth_item_child_parent', 'auth_item_child', 'parent', 'auth_item', 'name', 'NO ACTION', 'NO ACTION');
        $this->addForeignKey('fk_auth_item_child_child', 'auth_item_child', 'child', 'auth_item', 'name', 'NO ACTION', 'NO ACTION');

        $this->createTable('auth_assignment', [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->string(64)->notNull(),
            'created_at' => $this->integer(),
            'PRIMARY KEY (item_name, user_id)',
        ]);
        $this->addForeignKey('fk_auth_assignment_item_name', 'auth_assignment', 'item_name', 'auth_item', 'name', 'NO ACTION', 'NO ACTION');

        // Insert default roles
        $this->insert('auth_item', [
            'name' => 'superadmin',
            'type' => 1,
            'description' => 'Super Administrator',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    public function down()
    {
        $this->dropTable('auth_assignment');
        $this->dropTable('auth_item_child');
        $this->dropTable('auth_item');
        $this->dropTable('auth_rule');
    }
} 