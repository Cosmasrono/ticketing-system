<?php

use yii\db\Migration;

/**
 * Class m241021_065454_insert_initial_roles_and_permissions
 */
class m241021_065454_insert_initial_roles_and_permissions extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('roles', ['name'], [
            ['admin'],
            ['developer'],
        ]);

        $this->batchInsert('permissions', ['name'], [
            ['manageUsers'],
            ['viewReports'],
            ['createTickets'],
            ['assignTickets'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('roles', ['name' => 'admin']);
        $this->delete('roles', ['name' => 'developer']);
        $this->delete('permissions', ['name' => 'manageUsers']);
        $this->delete('permissions', ['name' => 'viewReports']);
        $this->delete('permissions', ['name' => 'createTickets']);
        $this->delete('permissions', ['name' => 'assignTickets']);
    }
}