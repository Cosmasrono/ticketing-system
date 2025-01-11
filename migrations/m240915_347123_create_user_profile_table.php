<?php

use yii\db\Migration;

class m240915_347123_create_user_profile_table extends Migration
{
    public function up()
    {
        $this->createTable('user_profile', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'entry_date' => $this->date()->notNull(),
            'leave_date' => $this->date(),
            'position' => $this->string(),
            'notes' => $this->text(),
            'attendance_status' => $this->string(50),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Add foreign key
        $this->addForeignKey(
            'fk-user_profile-user_id',
            'user_profile',
            'user_id',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('user_profile');
    }
} 