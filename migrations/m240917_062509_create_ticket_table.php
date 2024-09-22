<?php

use yii\db\Migration;

class m240917_062509_create_ticket_table extends Migration
{
     
    public function up()
    {
        $this->createTable('ticket', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->text()->notNull(),
            'status' => "ENUM('Pending', 'Approved', 'Cancelled') NOT NULL DEFAULT 'Pending'",
            'assigned_to' => $this->integer(),
            'company_email' => $this->string()->notNull(),
            'created_at' => $this->timestamp()->defaultValue(new Expression('CURRENT_TIMESTAMP')),
            'updated_at' => $this->timestamp()->defaultValue(new Expression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')),
        ]);
        if (!Yii::$app->db->schema->getTableSchema('ticket', true)->getColumn('assigned_to')) {
            $this->addColumn('ticket', 'assigned_to', $this->integer()->after('status'));
        }
    }

    public function down()
    {
        $this->dropTable('ticket');
    }
}

   