<?php

use yii\db\Migration;

class m240402_656879_update_ticket_status extends Migration
{
    public function up()
    {
        // First drop the index
        $this->dropIndex('idx-ticket-status', 'ticket');

        // Drop the default constraint using raw SQL
        $this->execute('DECLARE @ConstraintName nvarchar(200)
        SELECT @ConstraintName = Name FROM SYS.DEFAULT_CONSTRAINTS
        WHERE PARENT_OBJECT_ID = OBJECT_ID(\'ticket\')
        AND PARENT_COLUMN_ID = (SELECT column_id FROM sys.columns
                               WHERE NAME = N\'status\'
                               AND object_id = OBJECT_ID(N\'ticket\'))
        IF @ConstraintName IS NOT NULL
            EXECUTE(\'ALTER TABLE ticket DROP CONSTRAINT \' + @ConstraintName)');

        // Now alter the column
        $this->alterColumn('ticket', 'status', $this->string(20));

        // Add the new default constraint
        $this->execute('ALTER TABLE ticket ADD CONSTRAINT DF_ticket_status DEFAULT \'pending\' FOR status');

        // Recreate the index
        $this->createIndex('idx-ticket-status', 'ticket', 'status');
    }

    public function down()
    {
        // Drop the index
        $this->dropIndex('idx-ticket-status', 'ticket');

        // Drop the default constraint
        $this->execute('DECLARE @ConstraintName nvarchar(200)
        SELECT @ConstraintName = Name FROM SYS.DEFAULT_CONSTRAINTS
        WHERE PARENT_OBJECT_ID = OBJECT_ID(\'ticket\')
        AND PARENT_COLUMN_ID = (SELECT column_id FROM sys.columns
                               WHERE NAME = N\'status\'
                               AND object_id = OBJECT_ID(N\'ticket\'))
        IF @ConstraintName IS NOT NULL
            EXECUTE(\'ALTER TABLE ticket DROP CONSTRAINT \' + @ConstraintName)');

        // Change back to integer
        $this->alterColumn('ticket', 'status', $this->integer());

        // Add default value
        $this->execute('ALTER TABLE ticket ADD CONSTRAINT DF_ticket_status DEFAULT 0 FOR status');

        // Recreate the index
        $this->createIndex('idx-ticket-status', 'ticket', 'status');
    }
} 