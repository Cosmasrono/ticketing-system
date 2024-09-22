
<?php
use yii\db\Migration;

class m240917_072031_update_ticket_table extends Migration
{
    public function up()
    {
        // Check if the column already exists before adding it
        if (!$this->db->schema->getTableSchema('ticket', true)->getColumn('assigned_to')) {
            $this->addColumn('ticket', 'assigned_to', $this->integer()->after('status'));
        }

        // Add foreign key constraint to the developer table
        $this->addForeignKey(
            'fk-ticket-assigned_to',
            'ticket',
            'assigned_to',
            'developer', // Replace with your developer table name
            'id',
            'SET NULL', // Action on delete
            'CASCADE'   // Action on update
        );
    }

    public function down()
    {
        // Drop foreign key constraint
        $this->dropForeignKey('fk-ticket-assigned_to', 'ticket');

        // Drop assigned_to column if it exists
        if ($this->db->schema->getTableSchema('ticket', true)->getColumn('assigned_to')) {
            $this->dropColumn('ticket', 'assigned_to');
        }
    }
}
