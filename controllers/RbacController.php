<?php

namespace app\controllers;

use Yii;
use yii\rbac\ManagerInterface;

class RbacController extends \yii\web\Controller
{
    /**
     * @var ManagerInterface
     */
    private $authManager;

    public function __construct($id, $module, ManagerInterface $authManager, $config = [])
    {
        $this->authManager = $authManager;
        parent::__construct($id, $module, $config);
    }

    public function actionAssignDeveloper($userId)
    {
        $auth = Yii::$app->authManager;

        // Get the developer role
        $developerRole = $auth->getRole('developer');
        if ($developerRole === null) {
            echo "Developer role does not exist.\n";
            return;
        }

        // Check if the user already has the developer role
        if ($auth->getAssignment('developer', $userId)) {
            echo "User $userId already has the developer role.\n";
            return;
        }

        // Assign the developer role to the user
        $auth->assign($developerRole, $userId);
        echo "Assigned developer role to user $userId.\n";
    }

    public function actionCheckDeveloper($userId)
    {
        $auth = Yii::$app->authManager;

        if ($auth->getAssignment('developer', $userId)) {
            echo "User $userId has the developer role.\n";
        } else {
            echo "User $userId does not have the developer role.\n";
        }
    }
}
