<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\base\InvalidConfigException;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // Create roles
        $roles = [
            'user' => 'Basic user role',
            'developer' => 'Developer role',
            'admin' => 'Administrator role',
        ];

        foreach ($roles as $roleName => $description) {
            $role = $auth->getRole($roleName);
            if ($role === null) {
                $role = $auth->createRole($roleName);
                $role->description = $description;
                $auth->add($role);
                echo "Created role: $roleName\n";
            } else {
                echo "Role already exists: $roleName\n";
            }
        }

        // Create permissions
        $createTicket = $auth->createPermission('createTicket');
        $createTicket->description = 'Create a ticket';
        $auth->add($createTicket);

        $viewTicket = $auth->createPermission('viewTicket');
        $viewTicket->description = 'View a ticket';
        $auth->add($viewTicket);

        // Assign permissions to roles
        $userRole = $auth->getRole('user');
        $developerRole = $auth->getRole('developer');
        $adminRole = $auth->getRole('admin');

        $auth->addChild($userRole, $createTicket);
        $auth->addChild($userRole, $viewTicket);
        $auth->addChild($developerRole, $userRole);
        $auth->addChild($adminRole, $developerRole);

        // Remove ticket permissions from admin role
        $auth->removeChild($adminRole, $createTicket);
        $auth->removeChild($adminRole, $viewTicket);

        echo "RBAC initialization completed.\n";
    }
}
