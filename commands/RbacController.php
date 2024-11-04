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
        $auth->removeAll(); // Clear previous permissions

        // Create permissions
        $viewDashboard = $auth->createPermission('viewDashboard');
        $viewDashboard->description = 'View developer dashboard';
        $auth->add($viewDashboard);

        // Create developer role
        $developer = $auth->createRole('developer');
        $developer->description = 'Developer';
        $auth->add($developer);

        // Assign permissions to developer role
        $auth->addChild($developer, $viewDashboard);
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
