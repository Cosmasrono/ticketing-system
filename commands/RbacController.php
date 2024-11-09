<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\User;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // Add existing permissions
        $createTicket = $auth->createPermission('createTicket');
        $createTicket->description = 'Create a ticket';
        $auth->add($createTicket);

        $updateTicket = $auth->createPermission('updateTicket');
        $updateTicket->description = 'Update a ticket';
        $auth->add($updateTicket);

        // Add escalation permission
        $escalateTicket = $auth->createPermission('escalateTicket');
        $escalateTicket->description = 'Escalate a ticket';
        $auth->add($escalateTicket);

        // Add new close ticket permission
        $closeTicket = $auth->createPermission('closeTicket');
        $closeTicket->description = 'Close a ticket';
        $auth->add($closeTicket);

        // Create roles if they don't exist
        $admin = $auth->getRole('admin');
        if (!$admin) {
            $admin = $auth->createRole('admin');
            $auth->add($admin);
        }

        $developer = $auth->getRole('developer');
        if (!$developer) {
            $developer = $auth->createRole('developer');
            $auth->add($developer);
        }

        $user = $auth->getRole('user');
        if (!$user) {
            $user = $auth->createRole('user');
            $auth->add($user);
        }

        // Add existing permissions to roles
        $auth->addChild($user, $createTicket);
        
        $auth->addChild($developer, $updateTicket);
        $auth->addChild($developer, $escalateTicket); // Add escalation permission to developer
        $auth->addChild($developer, $closeTicket); // Add new close permission to developer

        // Admin inherits all permissions
        $auth->addChild($admin, $user);
        $auth->addChild($admin, $developer);

        echo "RBAC initialization completed.\n";
    }

    public function actionAssignDeveloperRole()
    {
        $auth = Yii::$app->authManager;
        $developerRole = $auth->getRole('developer');
        
        // Get all users with developer role
        $developers = User::find()->where(['role' => 'developer'])->all();
        
        foreach ($developers as $developer) {
            if (!$auth->getAssignment('developer', $developer->id)) {
                $auth->assign($developerRole, $developer->id);
            }
        }
    }
}
