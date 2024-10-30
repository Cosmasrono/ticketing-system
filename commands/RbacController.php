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
        $admin = $auth->createRole('admin');
        $superadmin = $auth->createRole('superadmin');
        $user = $auth->createRole('user');
        $developer = $auth->createRole('developer');

        // Add roles to auth manager
        $auth->add($admin);
        $auth->add($superadmin);
        $auth->add($user);
        $auth->add($developer);

        // Create permissions
        $manageTickets = $auth->createPermission('manageTickets');
        $manageTickets->description = 'Manage tickets';
        $auth->add($manageTickets);

        // Assign permissions to roles
        $auth->addChild($user, $manageTickets);
        $auth->addChild($developer, $manageTickets);

        // Note: admin and superadmin deliberately not given ticket permissions

        echo "RBAC initialization completed.\n";
    }
}
