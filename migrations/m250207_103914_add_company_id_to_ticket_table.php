<?php

use yii\db\Migration;

/**
 * Class m250207_103914_add_company_id_to_ticket_table
 */
class m250207_103914_add_company_id_to_ticket_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('ticket', 'company_id', $this->integer()->after('id'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {       
         $this->dropColumn('ticket', 'company_id');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        // Add the company_id column to the ticket table
        $this->addColumn('ticket', 'company_id', $this->integer()->after('id'));

        // Optionally, add a foreign key constraint if there's a related company table
        // Uncomment the following line if you want to enforce referential integrity
        // $this->addForeignKey('fk-ticket-company_id', 'ticket', 'company_id', 'company', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        // Remove the foreign key constraint if it was added
        // Uncomment the following line if you added the foreign key
        // $this->dropForeignKey('fk-ticket-company_id', 'ticket');

        // Drop the company_id column
        $this->dropColumn('ticket', 'company_id');
    }
    */
}
