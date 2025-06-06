<?php

use yii\db\Migration;

class m250124_075518_create_contracts_table extends Migration
{
    public function safeUp()
    {
        // Check if the table already exists
        if ($this->db->schema->getTableSchema('contracts', true) === null) {
            $this->createTable('contracts', [
                'id' => $this->primaryKey(),
                'client_id' => $this->integer()->notNull(),
                'type' => $this->string()->notNull(),
                'start_date' => $this->date()->notNull(),
                'end_date' => $this->date()->notNull(),
                'status' => $this->string()->notNull(),
                'value' => $this->decimal(10, 2)->notNull(),
                'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            ]);

            // Add foreign key for client_id
            $this->addForeignKey(
                'fk-contracts-client_id',
                'contracts',
                'client_id',
                'client',
                'id',
                'CASCADE',
                'CASCADE'
            );
        } else {
            echo "Table 'contracts' already exists.\n";
        }
    }

    public function down()
    {
        // Drop the table if it exists
        if ($this->db->schema->getTableSchema('contracts', true) !== null) {
            $this->dropTable('contracts');
        }
    }
}